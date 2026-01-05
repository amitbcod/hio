<?php

use App\Application\Actions\Checkout\UpdateTaxAndShippingCharges;

Class DbCheckout{
	private $dbl;

	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}



	 public function getQuoteDataById($shopcode,$quote_id)
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.sales_quote where `quote_id` = '$quote_id'";
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

	 public function getQuoteItemDataById($shopcode,$item_id)
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.sales_quote_items where `item_id` = '$item_id'";
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

	 public function get_sales_quote_items($quote_id)
	 {

		 $sql =  "SELECT * FROM sales_quote_items where `quote_id` = '$quote_id' order by item_id ASC ";
		 $row  = $this->dbl->dbl_conn->rawQuery($sql);
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

	function removeQuote($shopcode,$quote_id,$customer_id=''){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		if($customer_id<=0){
			$params=array($quote_id);
			$delete_row = $this->dbl->dbl_conn->rawQueryOne("DELETE FROM $shop_db.sales_quote_items where quote_id = ? ",$params);
			$delete_row = $this->dbl->dbl_conn->rawQueryOne("DELETE FROM $shop_db.sales_quote_address where quote_id = ? ",$params);
			$delete_row = $this->dbl->dbl_conn->rawQueryOne("DELETE FROM $shop_db.sales_quote_payment where quote_id = ? ",$params);
			$delete_row = $this->dbl->dbl_conn->rawQueryOne("DELETE FROM $shop_db.sales_quote where quote_id = ? ",$params);
		}else{

			$params=array($quote_id);
			$params_one=array($quote_id,$customer_id);
			$delete_row = $this->dbl->dbl_conn->rawQueryOne("DELETE FROM $shop_db.sales_quote_items where quote_id = ?  ",$params);
			$delete_row = $this->dbl->dbl_conn->rawQueryOne("DELETE FROM $shop_db.sales_quote_address where quote_id = ? ",$params);
			$delete_row = $this->dbl->dbl_conn->rawQueryOne("DELETE FROM $shop_db.sales_quote_payment where quote_id = ? ",$params);
			$delete_row = $this->dbl->dbl_conn->rawQueryOne("DELETE FROM $shop_db.sales_quote where quote_id = ? OR customer_id = ? ",$params_one);

		}

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				return false;
			}
		} else {
			return false;
		}
	}

	function updateQuoteCustomer($shopcode,$quote_id,$session_id,$customer_id,$checkout_method=''){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		if(isset($customer_id) && $customer_id>0){
			$customer_is_guest=0;
		}else{
			$customer_is_guest=1;
		}
		$params=array($session_id,$customer_id,$checkout_method,$customer_is_guest,time(),$quote_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  $shop_db.sales_quote set session_id = ?, customer_id = ?, checkout_method = ?,customer_is_guest = ?,  updated_at = ?  where quote_id= ? ",$params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				return false;
			}
		} else {
			return false;
		}
	}

	public function add_to_sales_order($shopcode,$shopid,$checkout_method,$customer_id,$customer_group_id,$customer_email,$customer_firstname,$customer_lastname,$applied_rule_ids,$coupon_code,$base_discount_amount,$base_grand_total,$base_shipping_amount,$base_shipping_tax_amount,$base_subtotal,$base_tax_amount,$discount_amount,$grand_total,$shipping_amount,$shipping_tax_amount,$shipping_charge,$shipping_tax_percent,$subtotal,$tax_amount,$total_qty_ordered,$voucher_code,$voucher_amount,$customer_is_guest,$invoice_self,$payment_tax_percent,$payment_charge,$payment_tax_amount,$payment_final_charge,$ship_method_id='',$ship_method_name='',$currency_name='',$currency_code_session='',$currency_conversion_rate='',$default_currency_flag='',$currency_symbol='', $order_barcode = null, $lang_code='',$lis_default_language='')
    {
        $shop_db = DB_NAME_SHOP_PRE . $shopcode; // constant variable
        $main_db = DB_NAME; //Constant variable

        $status = 7;  //pending

        if($shopcode == 'shop1') {
			$increment_id=$this->generate_new_transaction_id($shopcode, $customer_id);
		} else {
			$increment_id=$this->generate_new_transaction_id($shopcode);
		}

        if (empty($order_barcode)){
            $order_barcode = $increment_id;
        } else {
            $increment_id = $order_barcode;
        }


		$params = array($increment_id,$order_barcode, $checkout_method,$customer_id,$customer_group_id,$customer_email,$customer_firstname,$customer_lastname,$applied_rule_ids,$coupon_code,$base_discount_amount,$base_grand_total,$base_shipping_amount,$base_shipping_tax_amount,$base_subtotal,$base_tax_amount,$discount_amount,$grand_total,$shipping_amount,$shipping_tax_amount,$shipping_charge,$shipping_tax_percent,$ship_method_id,$ship_method_name,$subtotal,$tax_amount,$total_qty_ordered,$voucher_code,$voucher_amount,$customer_is_guest,$invoice_self,$payment_tax_percent,$payment_charge,$payment_tax_amount,$payment_final_charge,$status,$currency_name,$currency_code_session,$currency_conversion_rate,$default_currency_flag,$currency_symbol,$lang_code,$lis_default_language,time(),$_SERVER['REMOTE_ADDR']);
		$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO $shop_db.sales_order (increment_id, order_barcode,checkout_method, customer_id,customer_group_id,customer_email,customer_firstname,customer_lastname,applied_rule_ids,coupon_code,base_discount_amount,base_grand_total,base_shipping_amount,base_shipping_tax_amount,base_subtotal,base_tax_amount,discount_amount,grand_total,shipping_amount,shipping_tax_amount,shipping_charge,shipping_tax_percent,ship_method_id,ship_method_name,subtotal,tax_amount,total_qty_ordered,voucher_code,voucher_amount,customer_is_guest,invoice_self,payment_tax_percent,payment_charge,payment_tax_amount,payment_final_charge,status,currency_name,currency_code_session,currency_conversion_rate,default_currency_flag,currency_symbol,language_code,is_default_language,created_at,ip) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", $params);


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


	 function generate_new_transaction_id($shopcode, $customerid='')
     {
		if($shopcode == 'shop1') {

			$sales_order = 'S';
			$customer_type_id = 1;
			if($customerid != '' && $customerid > 0) {
				$customer_type_id = $this->getCustomerTypeId($shopcode, $customerid);
			}

			$getYears = date("y");
			$getRandomLetters = getRandomLetters(2);

			$getExtrazero = 0;

			$order_id='';
	        $user_transaction_id = $this->get_last_order_id($shopcode, 'actual');

			if($user_transaction_id==false)
			{
				$order_id = 1;

			}else{
				$last_order_id		= $user_transaction_id['order_id'];
				$order_id         = $last_order_id+1;
			}

			$GetOrderwithZero = getOrderIdwithExtrazero($order_id);

			$transaction_id = $sales_order.$customer_type_id."-".$getYears."-".$getRandomLetters.$getExtrazero.$GetOrderwithZero;
	        return $transaction_id;

		} else {
			$payment_id='';
			$user_transaction_id = $this->get_last_order_id($shopcode);

			if($user_transaction_id==false)
			{
				$payment_id        = 1001;

			}else{
				$last_inc_id		= $user_transaction_id['increment_id'];
				$payment_id         = $last_inc_id+1;
			}

			$transaction_id = $payment_id;
			return $transaction_id;
		}
     }

	 public function getCustomerTypeId($shopcode, $customer_id)
     {
     	$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$sql =  "SELECT customer_type_id FROM $shop_db.customers where `id` = '$customer_id' ";

		$row  = $this->dbl->dbl_conn->rawQueryOne($sql);

		$row  = $this->dbl->dbl_conn->rawQueryOne($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $row['customer_type_id'];
			}else{
				return false;
			}
		}else{
			return false;
		}
     }

	 public function get_last_order_id($shopcode, $flag='')
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		if($flag == 'actual') {
		 $sql =  "SELECT order_id,increment_id FROM $shop_db.sales_order order by order_id DESC limit 0,1";
		} else {
		 $sql =  "SELECT order_id,increment_id FROM $shop_db.sales_order where `parent_id` = '0' AND `main_parent_id` = '0'  order by order_id DESC limit 0,1";
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

	 public function add_to_sales_order_item($shopcode,$shopid,$order_id,$product_type, $product_inv_type,$product_id,$product_name,$product_code,$quantity,$sku,$barcode,$price,$total_price,$pro_shop_id,$created_by='',$parent_product_id='',$product_variants='',$estimate_delivery_time='',$applied_rule_ids='',$tax_percent='',$tax_amount='',$discount_amount='',$discount_percent='',$total_discount_amount='',$isPrelaunch='',$coupon_code='',$zumba_shop_flag='',$bundle_child_details='',$ship_method_id='',$shipping_tax_percent='')
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$created_by_type=0;

		$product_data = $this->get_product_to_be_returned_details($shopcode,$product_id);
		if($coupon_code=='ZSBC10' && $zumba_shop_flag == 2){
			$can_be_returned_data = 0;
		}else{
			$can_be_returned_data = $product_data[0]['can_be_returned'];
		}
		// $can_be_returned_data = 4;

		$is_bundle_item = 0;
		if($product_type == 'bundle') {
			$is_bundle_item = 1;
		}


		if($parent_product_id!=''){
			$params = array($order_id, $product_type,$product_inv_type, $product_id, $product_name,$product_code, $quantity,$sku, $barcode,$price, $total_price,$pro_shop_id,$parent_product_id, $product_variants,$estimate_delivery_time,time(),$created_by,$created_by_type,$_SERVER['REMOTE_ADDR'],$applied_rule_ids,$tax_percent,$tax_amount,$discount_amount,$discount_percent,$total_discount_amount,$isPrelaunch,$can_be_returned_data,$is_bundle_item);
			$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO $shop_db.sales_order_items (order_id,product_type,product_inv_type,product_id,product_name,product_code, qty_ordered,sku,barcode,price, total_price,shop_id,parent_product_id,product_variants,estimate_delivery_time,created_at,created_by,created_by_type,ip,applied_rule_ids,tax_percent,tax_amount,discount_amount,discount_percent,total_discount_amount,prelaunch,can_be_returned,is_bundle_item) VALUES (?, ?, ?,?, ?,?, ?,?, ?, ?,?, ?, ?,?, ?, ?,?,?, ?, ?,?,?,?,?,?,?,?,?)", $params);

		}else{
			$params = array($order_id, $product_type,$product_inv_type,$product_id, $product_name,$product_code, $quantity,$sku, $barcode,$estimate_delivery_time,$price, $total_price,$pro_shop_id,time(),$created_by,$created_by_type,$_SERVER['REMOTE_ADDR'],$applied_rule_ids,$tax_percent,$tax_amount,$discount_amount,$discount_percent,$total_discount_amount,$isPrelaunch,$can_be_returned_data,$is_bundle_item);
			$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO $shop_db.sales_order_items (order_id,product_type,product_inv_type,  product_id, product_name,product_code, qty_ordered,sku,barcode,estimate_delivery_time,price,total_price,shop_id,created_at,created_by,created_by_type,ip,applied_rule_ids,tax_percent,tax_amount,discount_amount,discount_percent,total_discount_amount,prelaunch,can_be_returned,is_bundle_item) VALUES (?, ?, ?,?, ?, ?,?, ?, ?,?, ?,?, ?,?,?,?, ?, ?,?,?,?,?,?,?,?,?)", $params);

		}


		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$last_insert_id = $this->dbl->dbl_conn->getInsertId();
			if ($this->dbl->dbl_conn->count > 0){


				if($product_type == 'bundle') {
					$webshop_obj = new DbProductFeature();
					$cart_obj = new DbCart();

					$BundleProductId = $last_insert_id;
					$bundlechildAry = json_decode($bundle_child_details, true);

					foreach($bundlechildAry as $bchild) {

						$bundle_child_product_id = $bchild['bundle_child_product_id'];

						$bc_product_id = $bchild['product_id'];
						$bc_parent_product_id = 0;
						$bc_parent_item_id = $BundleProductId;
						$bc_bundle_child_product_id = $bchild['bundle_child_product_id'];

						$ProductData=$webshop_obj->getproductDetailsById($shopcode,$shopid,$bc_product_id);

						$bc_product_type = $ProductData['product_type'];
						$bc_sku = $ProductData['sku'];
						$bc_barcode = $ProductData['barcode'];

						if($bc_product_type == 'conf-simple') {
							$bc_parent_product_id = $ProductData['parent_id'];
							$ParentProductData=$webshop_obj->getproductDetailsById($shopcode,$shopid,$bc_parent_product_id);

							$bc_product_name = $ParentProductData['name'];
							$bc_product_code = $ParentProductData['product_code'];

							$VariantInfo=$cart_obj->get_product_variant_details($shopcode,$bc_parent_product_id,$bc_product_id);

							$product_variants_arr=array();
							$bc_product_variants='';

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
								$bc_product_variants=json_encode($product_variants_arr);
							}else{
								$bc_product_variants='';
							}

						} else {
							$bc_product_name = $ProductData['name'];
							$bc_product_code = $ProductData['product_code'];
							$bc_product_variants = '';
						}

						$bc_product_inv_type = $product_inv_type;
						$bc_qty_ordered = $bchild['default_qty'] * $quantity;
						$bc_estimate_delivery_time = $estimate_delivery_time;
						$bc_prelaunch = $isPrelaunch;
						$bc_can_be_returned = $can_be_returned_data;
						$bc_bundle_qty_details = json_encode(array("default_qty" => $bchild['default_qty'], "ordered_qty" => $quantity));
						$bc_is_bundle_item = 1;


						$bc_shop_id = $pro_shop_id;
						$bc_order_id = $order_id;

						$bc_price = '';
						if($ship_method_id != NULL && $ship_method_id != '' && $ship_method_id > 0) {
							$bc_price = ($bchild['price'] * $shipping_tax_percent/100) + $bchild['price'];
							$bc_tax_percent = $shipping_tax_percent;
						}else{
							$bc_price = $bchild['webshop_price'];
							$bc_tax_percent = $bchild['tax_percent'];
						}

						$bc_total_price = $bc_price * $bc_qty_ordered;
						$bc_applied_rule_ids = '';

						$bc_discount_percent = $discount_percent;
						$bc_tax_amount = 0;

						if($coupon_code != "" && $bc_price > 0.00 && $bc_discount_percent > 0.00) {
							$pro_price_incl_tax = $bc_price - ($bc_price*$bc_discount_percent)/100;
						} else {
							$pro_price_incl_tax = $bc_price;
						}
						if($bc_tax_percent > 0.00 && $pro_price_incl_tax > 0.00) {
							$pro_price_excl_tax = $pro_price_incl_tax / ((100+$bc_tax_percent)/100);
							$bc_tax_amount =  $pro_price_incl_tax - $pro_price_excl_tax;
						}

						$bc_discount_amount = $bc_price*($bc_discount_percent / 100);
						$bc_total_discount_amount = $bc_discount_amount*$bc_qty_ordered;


						$params = array($bc_order_id, $bc_product_type,$bc_product_inv_type, $bc_product_id, $bc_product_name,$bc_product_code, $bc_qty_ordered,$bc_sku, $bc_barcode,$bc_price, $bc_total_price,$bc_shop_id,$bc_parent_product_id, $bc_product_variants,$bc_estimate_delivery_time,time(),$created_by,$created_by_type,$_SERVER['REMOTE_ADDR'],$bc_applied_rule_ids,$bc_tax_percent,$bc_tax_amount,$bc_discount_amount,$bc_discount_percent,$bc_total_discount_amount,$bc_prelaunch,$bc_can_be_returned,$bc_is_bundle_item,$bc_bundle_child_product_id,$bc_bundle_qty_details,$bc_parent_item_id);
						$this->dbl->dbl_conn->rawQuery("INSERT INTO $shop_db.sales_order_items (order_id,product_type,product_inv_type,product_id,product_name,product_code, qty_ordered,sku,barcode,price, total_price,shop_id,parent_product_id,product_variants,estimate_delivery_time,created_at,created_by,created_by_type,ip,applied_rule_ids,tax_percent,tax_amount,discount_amount,discount_percent,total_discount_amount,prelaunch,can_be_returned,is_bundle_item,bundle_child_product_id,bundle_qty_details,parent_item_id) VALUES (?, ?, ?,?, ?,?, ?,?, ?, ?,?, ?, ?,?, ?, ?,?,?, ?, ?,?,?,?,?,?,?,?,?,?,?,?)", $params);
					}

					return $BundleProductId;

				} else {

					return $last_insert_id;
				}


			}else{
				return false;
			}
		} else {
//			 echo 'Insert in UST failed. Error: '. $this->dbl->dbl_conn->getLastError();
			return false;
		}
	}


	public function get_customer_address_by_id($shopcode,$customer_id,$address_id){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.customers_address where `customer_id` = '$customer_id'  AND id='$address_id' ";
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

	public function add_to_sales_order_address($shopcode,$shopid,$order_id,$address_type,$shipping_first_name,$shipping_last_name,$address_options,$shipping_mobile_no,$shipping_address,$shipping_address_1,$shipping_city,$shipping_state,$shipping_country,$shipping_pincode,$save_in_address_book,$company_name='',$vat_no='',$consulation_no='',$res_company_name='',$res_company_address='',$vat_vies_valid_flag='',$same_as_billing=''){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		  $same_as_billing = (bool) ($same_as_billing ?? 0);

		$params = array($order_id, $address_type,$shipping_first_name, $shipping_last_name,$address_options, $shipping_mobile_no,$shipping_address,$shipping_address_1, $shipping_city,$shipping_state,$shipping_country,$shipping_pincode,time(),$_SERVER['REMOTE_ADDR'],$save_in_address_book,$company_name,$vat_no,$consulation_no,$res_company_name,$res_company_address,$vat_vies_valid_flag,$same_as_billing);

		$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO $shop_db.sales_order_address (order_id,address_type,  first_name, last_name,customer_address_id, mobile_no,address_line1,address_line2,city,  state,country,pincode,created_at,ip,save_in_address_book,company_name,vat_no,consulation_no,res_company_name,res_company_address,vat_vies_valid_flag,same_as_billing) VALUES (?, ?, ?,?, ?, ?,?, ?, ?,?, ?,?, ?,?,?, ?,?, ?,?,?,?,?)", $params);

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

	function updateOrderCustomerInfo($shopcode,$order_id,$customer_email,$customer_firstname,$customer_lastname){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$params=array($customer_email,$customer_firstname,$customer_lastname,$order_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  $shop_db.sales_order set customer_email = ?, customer_firstname = ?, customer_lastname = ?  where order_id= ? ",$params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				return false;
			}
		} else {
			return false;
		}
	}

	 public function getOrderDataById($shopcode,$order_id)
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.sales_order where `order_id` = '$order_id'";
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

	 function updateCheckoutMethod($shopcode,$quote_id,$checkout_method){
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$params=array($checkout_method,time(),$quote_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  $shop_db.sales_quote set checkout_method = ? updated_at = ? where quote_id= ? ",$params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				return false;
			}
		} else {
			return false;
		}
	 }




	  function updateShippingQuoteAddress($shopcode,$quote_id,$address_type,$shipping_first_name,$shipping_last_name,$address_options,$shipping_mobile_no,$shipping_address,$shipping_address_1,$shipping_city,$shipping_state,$shipping_country,$shipping_pincode,$save_in_address_book,$company_name='', $vat_no='', $consulation_no='', $res_company_name='', $res_company_address='',$vat_vies_valid_flag='',$same_as_billing=''){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$same_as_billing = (bool) ($same_as_billing ?? 0);

		//$params=array($shipping_first_name,$shipping_last_name,$address_options,$shipping_mobile_no,$shipping_address,$shipping_address_1,$shipping_city,$shipping_state,$shipping_country,$shipping_pincode,time(),$_SERVER['REMOTE_ADDR'],$save_in_address_book,$quote_id);

		//$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  $shop_db.sales_quote_address set first_name = ?, last_name = ?,customer_address_id = ?, mobile_no =? ,address_line1= ?,address_line2 = ? ,city = ? ,  state= ? ,country = ?,pincode = ?,updated_at = ? ,ip= ?,save_in_address_book= ? where quote_id= ? ",$params);

		if($address_type == 2){

			$company_name ='';
			$vat_no='';
			$consulation_no='';
			 $res_company_name='';
			$res_company_address='';
			$vat_vies_valid_flag='';

			$params=array($shipping_first_name,$shipping_last_name,$address_options,$shipping_mobile_no,$shipping_address,$shipping_address_1,$shipping_city,$shipping_state,$shipping_country,$shipping_pincode,$company_name, $vat_no, $consulation_no, $res_company_name, $res_company_address, $vat_vies_valid_flag, time(),$_SERVER['REMOTE_ADDR'],$save_in_address_book,$same_as_billing,$quote_id,$address_type);

			$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  $shop_db.sales_quote_address set first_name = ?, last_name = ?,customer_address_id = ?, mobile_no =? ,address_line1= ?,address_line2 = ? ,city = ? ,  state= ? ,country = ?,pincode = ?, company_name = ?, vat_no = ?, consulation_no = ?, res_company_name = ?, res_company_address = ?, vat_vies_valid_flag = ?, updated_at = ? ,ip= ?,save_in_address_book= ?,same_as_billing= ? where quote_id= ? and address_type=?",$params);

		  }else{

			$params=array($shipping_first_name,$shipping_last_name,$address_options,$shipping_mobile_no,$shipping_address,$shipping_address_1,$shipping_city,$shipping_state,$shipping_country,$shipping_pincode,$company_name, $vat_no, $consulation_no, $res_company_name, $res_company_address, $vat_vies_valid_flag, time(),$_SERVER['REMOTE_ADDR'],$save_in_address_book,$same_as_billing,$quote_id,$address_type);

			$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  $shop_db.sales_quote_address set first_name = ?, last_name = ?,customer_address_id = ?, mobile_no =? ,address_line1= ?,address_line2 = ? ,city = ? ,  state= ? ,country = ?,pincode = ?, company_name = ?, vat_no = ?, consulation_no = ?, res_company_name = ?, res_company_address = ?, vat_vies_valid_flag = ?,updated_at = ? ,ip= ?,save_in_address_book= ?,same_as_billing= ? where quote_id= ? and address_type=?",$params);

		  }


		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				return false;
			}
		} else {
			return false;
		}
	 }

	 public function getQuoteShippingAddressById($shopcode,$quote_id,$address_type)
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.sales_quote_address where `quote_id` = '$quote_id' AND address_type= $address_type ";
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

	 function updateQuoteCustomerInfo($shopcode,$quote_id,$customer_email,$customer_firstname,$customer_lastname){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$params=array($customer_email,$customer_firstname,$customer_lastname,$quote_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  $shop_db.sales_quote set customer_email = ?, customer_firstname = ?, customer_lastname = ?  where quote_id= ? ",$params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				return false;
			}
		} else {
			return false;
		}
	}

	function getPaymentMethods($shopcode,$country_code){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		//$params=array($country_code);

		$query = "SELECT wp.*, pm.payment_gateway,pm.payment_gateway_key,pm.payment_type,pm.id as main_payment_id FROM $shop_db.webshop_payments as wp LEFT JOIN $main_db.payment_master as pm ON wp.payment_id = pm.id WHERE (pm.country  = '$country_code' OR pm.country ='')  AND  wp.status = 1 AND wp.integrate_with_ws = 1 order by pm.id asc ";

  		$Result = $this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $Result;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	 public function getMainShopPaymentDetailsById($id)
	 {

		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $main_db.payment_master where `id` = '$id'";
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

	 public function getWebShopPaymentDetailsById($shopcode,$payment_id)
	 {
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.webshop_payments where `payment_id` = '$payment_id'";
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

	 public function getQuotePaymentById($shopcode,$quote_id)
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.sales_quote_payment where `quote_id` = '$quote_id'";
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


	  function updateQuotePaymentMethod($shopcode,$quote_id,$payment_method_id,$payment_method,$payment_method_name,$payment_type,$gateway_details){
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$params=array($payment_method_id,$payment_method,$payment_method_name,$payment_type,$gateway_details,time(),$quote_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  $shop_db.sales_quote_payment set payment_method_id = ?, payment_method = ?,payment_method_name= ?, payment_type=?,gateway_details=?,  updated_at = ? where quote_id= ? ",$params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				return false;
			}
		} else {
			return false;
		}
	 }

	 public function getQuotePaymentDataById($shopcode,$quote_id)
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.sales_quote_payment where `quote_id` = '$quote_id'";
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


	 function add_to_sales_order_payment($shopcode,$shopid,$order_id,$payment_method_id,$payment_method,$payment_method_name,$payment_type,$currency_code){

		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable
		$params = array($order_id,$payment_method_id,$payment_method,$payment_method_name,$payment_type,$currency_code,time(),$_SERVER['REMOTE_ADDR']);

		$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO $shop_db.sales_order_payment (order_id,payment_method_id,  payment_method, payment_method_name,payment_type, currency_code,created_at,ip) VALUES (?, ?, ?,?, ?, ?,?, ?)", $params);

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




	 public function getOrderItems($shopcode,$order_id)
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.sales_order_items where `order_id` = '$order_id' AND product_type != 'bundle'";
		 $Result  = $this->dbl->dbl_conn->rawQuery($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $Result;
			}else{
				return false;
			}
		}else{
			return false;
		}

	 }

	  public function getFormattedOrderAddressById($shopcode,$order_id,$address_type)
	 {
		 $address='';
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		 $main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.sales_order_address where `order_id` = $order_id AND address_type = $address_type ";
		 $row_address  = $this->dbl->dbl_conn->rawQueryOne($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				$address='<span>'.$row_address['first_name'].' '.$row_address['last_name'].'<br>'.$row_address['address_line1'].' '.$row_address['address_line2'].'<br>'.$row_address['city'].' '.$row_address['state'].'<br>'.$row_address['country'].'-'.$row_address['pincode'].'</span>';
				return $address;
			}else{
				return $address;
			}
		}else{
			return $address;
		}

	 }

	  public function getOrderPaymentDataById($shopcode,$order_id)
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.sales_order_payment where `order_id` = '$order_id'";
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

	  function updateOrderEmailSentNotification($shopcode,$order_id){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$params=array(time(),$order_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  $shop_db.sales_order set email_sent = 1, updated_at = ?  where order_id= ? ",$params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				return false;
			}
		} else {
			return false;
		}
	}

	function updateOrderStatus($shopcode,$order_id,$status){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$params=array($status,time(),$order_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  $shop_db.sales_order set status = ?, updated_at = ?  where order_id= ? ",$params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				return false;
			}
		} else {
			return false;
		}
	}


	function getFbcUserIdByShopId($shopid){
		$main_db = DB_NAME; //Constant variable

		 $sql =  "SELECT * FROM $main_db.fbc_users where `shop_id` = '$shopid' AND parent_id = 0";
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

	function add_to_order_log($type,$order_id,$shopid,$fbc_user_id){
		$main_db = DB_NAME; //Constant variable

		$params = array($type,$order_id, $shopid,$fbc_user_id,time(),$_SERVER['REMOTE_ADDR']);
		$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO $main_db.orders_logs (type,order_id,shop_id,fbc_user_id,created_at,ip) VALUES (?, ?, ?,?, ?, ?)", $params);

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

	function getProductsDropShipAndVirtualWithQtyZero($shopcode,$shopid,$order_id,$seller_shop_id){


		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($order_id,$seller_shop_id);
		/* old code
		$query = "SELECT oi.item_id,oi.price,oi.qty_ordered,oi.total_price,p.shop_id,p.shop_product_id FROM $shop_db.sales_order_items as oi  INNER JOIN $shop_db.products as p ON oi.product_id = p.id LEFT JOIN $shop_db.products_inventory as pi ON oi.product_id = pi.product_id WHERE p.product_inv_type IN ('dropship','virtual') AND pi.qty <=0 and oi.order_id = ? AND p.shop_product_id > 0 AND p.shop_id= ? ";
		*/

		$query = "SELECT oi.item_id,oi.price,oi.qty_ordered,oi.total_price,p.shop_id,p.shop_product_id FROM $shop_db.sales_order_items as oi  INNER JOIN $shop_db.products as p ON oi.product_id = p.id LEFT JOIN $shop_db.products_inventory as pi ON oi.product_id = pi.product_id WHERE p.product_inv_type IN ('dropship') AND  oi.order_id = ? AND p.shop_product_id > 0 AND p.shop_id= ? ";

  		$product_variant_master = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $product_variant_master;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	function getShopsForBTwoBOrders($shopcode,$shopid,$order_id){


		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($order_id);

		/*
		$query = "SELECT p.shop_id FROM $shop_db.sales_order_items as oi  INNER JOIN $shop_db.products as p ON oi.product_id = p.id LEFT JOIN $shop_db.products_inventory as pi ON oi.product_id = pi.product_id WHERE p.product_inv_type IN ('dropship','virtual') AND pi.qty <=0 and oi.order_id = ? AND p.shop_product_id > 0 group by p.shop_id";
		*/

		$query = "SELECT p.shop_id FROM $shop_db.sales_order_items as oi  INNER JOIN $shop_db.products as p ON oi.product_id = p.id LEFT JOIN $shop_db.products_inventory as pi ON oi.product_id = pi.product_id WHERE p.product_inv_type IN ('dropship') AND  oi.order_id = ? AND p.shop_product_id > 0 group by p.shop_id";

  		$product_variant_master = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $product_variant_master;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}


	//new
	function getShopsForBTwoBOrdersMultiple($shopcode,$shopid,$order_ids){


		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		//$param = array($order_id);
		$order_ids_string = implode(',',$order_ids);
		/*
		$query = "SELECT p.shop_id FROM $shop_db.sales_order_items as oi  INNER JOIN $shop_db.products as p ON oi.product_id = p.id LEFT JOIN $shop_db.products_inventory as pi ON oi.product_id = pi.product_id WHERE p.product_inv_type IN ('dropship','virtual') AND pi.qty <=0 and oi.order_id = ? AND p.shop_product_id > 0 group by p.shop_id";
		*/

		$query = "SELECT oi.order_id,p.shop_id FROM $shop_db.sales_order_items as oi  INNER JOIN $shop_db.products as p ON oi.product_id = p.id LEFT JOIN $shop_db.products_inventory as pi ON oi.product_id = pi.product_id WHERE p.product_inv_type IN ('dropship') AND  oi.order_id IN ($order_ids_string) AND p.shop_product_id > 0 ";

  		$product_variant_master = $this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $product_variant_master;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}


	//end new

	function getB2BOrderItemsByIds($shopcode,$shopid,$order_id,$item_ids){


		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($order_id);
		$query = "SELECT * FROM $shop_db.sales_order_items where order_id=? AND item_id IN ($item_ids)";

  		$Result = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $Result;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	function generate_new_b2b_transaction_id($shopcode,$shopid)
     {
		$payment_id='';
        $user_transaction_id = $this->get_last_b2b_order_id($shopcode,$shopid);

		if($user_transaction_id==false)
		{
			$payment_id        = '1001';

		}else{
			$last_inc_id		= $user_transaction_id['increment_id'];
			$last_order_id		= str_replace('B2B-','',$last_inc_id);
			$payment_id         = $last_order_id+1;
		}

		$transaction_id = 'B2B-'.$payment_id;
        return $transaction_id;
     }



	  public function get_last_b2b_order_id($shopcode,$shopid)
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT order_id,increment_id FROM $shop_db.b2b_orders where `parent_id` = '0' AND `main_parent_id` = '0'  order by order_id DESC limit 0,1";
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

	 public function create_b2b_order_for_webshop($shopid,$webshop_order_id,$seller_shopcode,$seller_shop_id,$customer_firstname,$customer_lastname,$customer_is_guest,$subtotal,$grand_total,$base_subtotal,$base_grand_total,$total_qty_ordered,$discount_percent,$discount_amount,$discount_amout_special_price=''){
		$shop_db =  DB_NAME_SHOP_PRE.$seller_shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$increment_id=$this->generate_new_b2b_transaction_id($seller_shopcode,$seller_shop_id);
		$order_barcode=$increment_id;
		$shipment_type=2;

		$status=0;  //to be process

		$discount_amout_special_price=isset($discount_amout_special_price)?$discount_amout_special_price:0.00;


		$params = array($webshop_order_id,$shopid,$increment_id, $order_barcode,$shipment_type,$customer_firstname,$customer_lastname,$customer_is_guest,$subtotal,$grand_total,$base_subtotal,$base_grand_total,$total_qty_ordered,$discount_percent,$discount_amount,$discount_amout_special_price,time(),$_SERVER['REMOTE_ADDR'],$status);

		$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO $shop_db.b2b_orders (webshop_order_id,shop_id,increment_id,order_barcode,shipment_type,customer_firstname,customer_lastname,customer_is_guest,subtotal,grand_total,base_subtotal,base_grand_total,total_qty_ordered,discount_percent,discount_amount,discount_amout_special_price,created_at,ip,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", $params);


		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$last_insert_id = $this->dbl->dbl_conn->getInsertId();
			if ($this->dbl->dbl_conn->count > 0){
				return $last_insert_id;
			}else{
				return false;
			}
		} else {
			//echo 'Insert in UST failed. Error: '. $this->dbl->dbl_conn->getLastError();
			return false;
		}
	}

	public function add_to_b2b_order_item($shopcode,$shopid,$order_id,$product_type, $product_id,$product_name,$product_code,$quantity,$sku,$barcode,$price,$total_price,$created_by='',$parent_product_id='',$product_variants='',$applied_rule_ids='',$tax_percent='',$tax_amount='',$discount_amount='',$webshop_shopid='',$special_price_flag='',$special_price_original_price='')
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$created_by_type=0;

		$tax_percent=isset($tax_percent)?$tax_percent:0;
		$tax_amount=isset($tax_amount)?$tax_amount:0;
		$discount_amount=isset($discount_amount)?$discount_amount:0;
		$applied_rule_ids=isset($applied_rule_ids)?$applied_rule_ids:'';
		$special_price_flag=isset($special_price_flag)?$special_price_flag:0;

		/*$SellerFbcData=$this->getFbcUserShopDataByShopId($shopid);

		$WebshopFbcData=$this->getFbcUserShopDataByShopId($webshop_shopid);

		$seller_shop_currency_code=$SellerFbcData['currency_code'];
		$buyer_shop_currency_code=$WebshopFbcData['currency_code'];

		if($seller_shop_currency_code!=$buyer_shop_currency_code){
			$price=$this->sis_convert_currency($buyer_shop_currency_code,$seller_shop_currency_code,$price);
			$total_price=$this->sis_convert_currency($buyer_shop_currency_code,$seller_shop_currency_code,$total_price);
			if($discount_amount>0){
				$discount_amount=$this->sis_convert_currency($buyer_shop_currency_code,$seller_shop_currency_code,$discount_amount);
			}

		}*/


		if($parent_product_id!=''){
			$params = array($order_id, $product_type, $product_id, $product_name,$product_code, $quantity,$sku, $barcode,$price, $total_price,$parent_product_id, $product_variants,time(),$created_by,$_SERVER['REMOTE_ADDR'],$applied_rule_ids,$tax_percent,$tax_amount,$discount_amount,$special_price_flag,$special_price_original_price);
			$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO $shop_db.b2b_order_items (order_id,product_type,product_id, product_name,product_code, qty_ordered,sku,barcode,price,  total_price,parent_product_id,product_variants,created_at,created_by,ip,applied_rule_ids,tax_percent,tax_amount,discount_amount,special_price_flag,special_price_original_price) VALUES (?, ?, ?,?, ?, ?,?, ?, ?,?, ?, ?,?, ?, ?,?,?, ?, ?,?,?)", $params);

		}else{
			$params2 = array($order_id, $product_type,$product_id, $product_name,$product_code, $quantity,$sku, $barcode,$price, $total_price,time(),$created_by,$_SERVER['REMOTE_ADDR'],$applied_rule_ids,$tax_percent,$tax_amount,$discount_amount,$special_price_flag,$special_price_original_price);

			$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO $shop_db.b2b_order_items (order_id,product_type,  product_id, product_name,product_code, qty_ordered,sku,barcode,price,  total_price,created_at,created_by,ip,applied_rule_ids,tax_percent,tax_amount,discount_amount,special_price_flag,special_price_original_price) VALUES (?, ?, ?,?, ?, ?,?, ?, ?,?, ?,?, ?,?,?, ?,?,?,?)", $params2);

		}


		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$last_insert_id = $this->dbl->dbl_conn->getInsertId();
			if ($this->dbl->dbl_conn->count > 0){
				return $last_insert_id;
			}else{
				return false;
			}
		} else {
			 echo 'Insert in UST failed. Error: '. $this->dbl->dbl_conn->getLastError();
			return false;
		}
	}

	public function update_b2b_order_for_webshop($shopid,$b2b_order_id,$seller_shopcode,$seller_shop_id,$tax_amount,$base_tax_amount,$grand_total,$base_grand_total){
		$shop_db =  DB_NAME_SHOP_PRE.$seller_shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$params=array($tax_amount,$base_tax_amount,$grand_total,$base_grand_total,$b2b_order_id);
		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.b2b_orders SET tax_amount = ?, base_tax_amount = ?, grand_total = ?, base_grand_total = ? WHERE order_id = ?",$params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			return true;
		} else {
			return false;
		}
	}


	function get_fbc_b2b_user_details($shop_id){
		$main_db = DB_NAME; //Constant variable

		 $sql =  "SELECT * FROM $main_db.fbc_users_b2b_details where `shop_id` = '$shop_id'";
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

	function get_b2b_customers_details($seller_shopcode,$seller_shop_id,$shopid){
		$shop_db =  DB_NAME_SHOP_PRE.$seller_shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		 $sql =  "SELECT t1.shop_id, t1.tax_exampted, t2.* FROM $shop_db.b2b_customers t1, $shop_db.b2b_customers_details t2 where t1.id=t2.customer_id and t1.`shop_id` = '$shopid'";
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


	function file_get_contents_curl($url) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);


		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}


	function sis_convert_currency($fromCurrency,$toCurrency,$amount) {
		if($fromCurrency==$toCurrency){
			return $amount;
		}

		$fromCurrency = urlencode($fromCurrency);
		$toCurrency = urlencode($toCurrency);
		$url  = "https://www.google.com/search?q=".$fromCurrency."+to+".$toCurrency;
		$get = $this->file_get_contents_curl($url);
		$get = explode('<div class="BNeawe iBp4i AP7Wnd">',$get);
		$get = explode("</div>",$get[2]);
		$price_per_one= preg_replace("/[^0-9,.]/", null, $get[0]);
		if( strpos($price_per_one, ',') !== false ) {
			$price_per_one=str_replace(',','.',$price_per_one);
		}

		$exhangeRate = $price_per_one;
		//$exhangeRate = $converted_currency;
		$convertedAmount = $amount*$exhangeRate;
		$convertedAmount = round($convertedAmount,2);
		return $convertedAmount;
	}

	function decrementAvailableQty($shopcode,$product_id,$qty_ordered){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$params=array($qty_ordered,$product_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.products_inventory SET available_qty = available_qty - ?  WHERE product_id = ?  ",$params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				return false;
			}
		} else {
			return false;
		}
	}

	function getMaxProductsTaxFromQuoteItems($shopcode,$shopid,$quote_id){


		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($quote_id);

		$query = "SELECT max(p.tax_percent) as tax_percent FROM $shop_db.sales_quote_items as oi  INNER JOIN $shop_db.products as p ON oi.product_id = p.id  WHERE oi.quote_id = ? ";

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

	function getMinProductsTaxFromQuoteItems($shopcode,$shopid,$quote_id){


		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($quote_id);

		$query = "SELECT min(p.tax_percent) as tax_percent FROM $shop_db.sales_quote_items as oi  INNER JOIN $shop_db.products as p ON oi.product_id = p.id  WHERE oi.quote_id = ? ";

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

	function getFootwearCatId($shopcode,$shopid){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$identifier='footwear_category_id';

		$param = array($identifier);

		$query = "SELECT * from $shop_db.custom_variables  WHERE identifier = ? ";

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

	public function get_product_details($shopcode,$product_id)
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.products where `id` = '$product_id' ";
		 $row  = $this->dbl->dbl_conn->rawQuery($sql);
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

	public function get_product_to_be_returned_details($shopcode,$product_id)
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT `can_be_returned` FROM $shop_db.products where `id` = '$product_id' ";
		 $row  = $this->dbl->dbl_conn->rawQuery($sql);
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

	function updateTaxAndShippingCharges($quote_id){
        return (new UpdateTaxAndShippingCharges())->execute($quote_id);
	}

	function finalupdateQuoteShippingChargeAndTax($shopcode,$shopid,$quote_id,$shipping_charge,$shipping_amount,$shipping_tax_percent,$shipping_tax_amount){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		$params=array($shipping_charge,$shipping_tax_percent,$shipping_tax_amount,$shipping_amount,time(),$quote_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  $shop_db.sales_quote set shipping_charge = ?, shipping_tax_percent = ?, shipping_tax_amount = ?,shipping_amount = ?,  updated_at = ?  where quote_id= ? ",$params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				//return false;
			}
		} else {
			//return false;
		}

	}

	function checkIsFootwear($shopcode,$shopid,$product_id,$footwear_category_id=''){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$flag=false;

		if(isset($footwear_category_id) && $footwear_category_id>0){
			//echo $footwear_category_id;exit;
			$param = array($product_id);

			$query = "SELECT * from $shop_db.products_category  WHERE product_id = ? order by level ASC ";

			$Categories = $this->dbl->dbl_conn->rawQuery($query,$param);

			if ($this->dbl->dbl_conn->getLastErrno() === 0){
				if ($this->dbl->dbl_conn->count > 0){
					if(isset($Categories) && count($Categories)>0){

						foreach($Categories as $cat){
							if($footwear_category_id==$cat['category_ids']){
								$flag=true;
								break;
							}else{
								$category_id=$cat['category_ids'];
								$level=$cat['level'];
								$CategoryDetail=$this->getCategoryDetail($shopcode,$shopid,$category_id,$level);
								if($CategoryDetail!=false){
									if(($CategoryDetail['cat_level']==0) && ($footwear_category_id==$cat['category_ids']))
									{
											$flag=true;
											break;
									}
									else if(($CategoryDetail['cat_level']==1) && ($CategoryDetail['parent_id']==$footwear_category_id))
									{
											$flag=true;
											break;
									}
									else if(($CategoryDetail['cat_level']==2) && ($CategoryDetail['parent_id']==$footwear_category_id || $CategoryDetail['main_parent_id']==$footwear_category_id))
									{
											$flag=true;
											break;
									}else{
										$flag=false;
									}
								}else{
									$flag=false;
								}

							}
						}

					}

					return $flag;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	function getCategoryDetail($shopcode,$shopid,$category_id,$level){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$identifier='footwear_category_id';

		$param = array($category_id);

		$query = "SELECT * from $main_db.category  WHERE id = ? ";

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

	function get_fbc_user_shop_details($shop_id){
		$main_db = DB_NAME; //Constant variable

		 $sql =  "SELECT * FROM $main_db.fbc_users_shop where `shop_id` = '$shop_id'";
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

	function incrementAvailableQty($shopcode,$product_id,$qty_ordered){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$params=array($qty_ordered,$product_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.products_inventory SET available_qty = available_qty + ?  WHERE product_id = ?  ",$params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				return false;
			}
		} else {
			return false;
		}
	}

	 public function getB2BOrderItemsByOrderId($seller_shopcode,$seller_shop_id,$b2b_order_id)
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$seller_shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.b2b_order_items where `order_id` = '$b2b_order_id'";
		 $row  = $this->dbl->dbl_conn->rawQuery($sql);
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

	public function getFbcUserShopDataByShopId($shopid){
        return $this->get_fbc_user_shop_details($shopid);
	}

	 public function getB2BOrderDataByOrderId($seller_shopcode,$b2b_order_id)
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$seller_shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.b2b_orders where `order_id` = '$b2b_order_id'";
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

	  function updateB2BOrderEmailSentNotification($seller_shopcode,$b2b_order_id){
		$shop_db =  DB_NAME_SHOP_PRE.$seller_shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$params=array(time(),$b2b_order_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  $shop_db.b2b_orders set email_sent = 1, updated_at = ?  where order_id= ? ",$params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				return false;
			}
		} else {
			return false;
		}
	}

	 public function getB2BOrderByWebshopOrderId($seller_shopcode,$seller_shop_id,$webshop_order_id,$webshop_shop_id)
	 {
		$shop_db =  DB_NAME_SHOP_PRE.$seller_shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.b2b_orders where `webshop_order_id` = '$webshop_order_id' and `shop_id` = '$webshop_shop_id'";
		 $row  = $this->dbl->dbl_conn->rawQueryOne($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $row;
			}else{
				//return false;
			}
		}else{
			//return false;
		}

	 }

	 //otp after verified success
	 function removeOtp($shopcode,$order_id){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		if($order_id){
			$params=array($order_id);
			$delete_row = $this->dbl->dbl_conn->rawQueryOne("DELETE FROM $shop_db.sales_order_cod_otp where order_id = ? ",$params);
		}

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				return false;
			}
		} else {
			return false;
		}
	}



	function update_vatDetails_in_sales_order($shopcode,$shopid,$order_id,$company_name='',$vat_no='',$consulation_no='',$res_company_name='',$res_company_address=''){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$params=array($company_name,$vat_no,$consulation_no,$res_company_name,$res_company_address,$order_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  $shop_db.sales_order set company_name = ?, vat_no = ?, consulation_no = ?,res_company_name = ?, res_company_address = ?  where order_id= ? ",$params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				return false;
			}
		} else {
			return false;
		}
	}


	function getShippingChargesByCountry($shopcode,$shopid,$shipping_country,$total_weight, $total_price = 0){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		  $param = array($total_weight,$shipping_country);

		 // $query = "SELECT * from $shop_db.products_category  WHERE product_id = ? order by level ASC ";
		$query =  "SELECT a.*, sm.ship_method_name FROM (SELECT DISTINCT * FROM $shop_db.shipping_methods_charges ORDER BY weight ) AS a left join $shop_db.shipping_methods AS sm on sm.id = a.ship_method_id WHERE a.weight > ? AND a.country_code= ? and sm.remove_flag = 0 GROUP BY ship_method_id";

		$shipping_methods_charges = $this->dbl->dbl_conn->rawQuery($query,$param);

        $shipping_methods_charges = array_map(function($shipping_methods_charge) use ($total_price) {
            if($shipping_methods_charge['cart_value_2'] !== null && $total_price > $shipping_methods_charge['cart_value_2']){
                $shipping_methods_charge['ship_rate'] = $shipping_methods_charge['rate_2'];
            }
            if($shipping_methods_charge['cart_value_3'] !== null && $total_price > $shipping_methods_charge['cart_value_3']){
                $shipping_methods_charge['ship_rate'] = $shipping_methods_charge['rate_3'];
            }
            return $shipping_methods_charge;
        }, $shipping_methods_charges);


		if ($this->dbl->dbl_conn->getLastErrno() === 0){

			if ($this->dbl->dbl_conn->count > 0){
				return $shipping_methods_charges;
			}else{
				return false;
			}
		} else {
			return false;
		}

	}


	function UpdateQuoteShippingIdName($shopcode,$shopid,$quote_id,$shipping_method_id='',$shipping_method_name=''){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$params=array($shipping_method_id,$shipping_method_name,$quote_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  $shop_db.sales_quote set ship_method_id = ?, ship_method_name = ? where quote_id= ? ",$params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				return false;
			}
		} else {
			return false;
		}
	}

	function getSpecialPricingB2b($shopcode,$buyer_shopid,$seller_product_id){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$params=array($seller_product_id,$buyer_shopid,time());

		$query = "SELECT * FROM $shop_db.products_special_prices_b2b where product_id = ? and shop_id = ? AND ? BETWEEN special_price_from and special_price_to";

		$products_special_prices_b2b = $this->dbl->dbl_conn->rawQueryOne($query,$params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){

			if ($this->dbl->dbl_conn->count > 0){
				return $products_special_prices_b2b;
			}else{
				return false;
			}
		} else {
			return false;
		}


	}

    public function get_default_billing_address($shopcode, int $customer_id){
        $shop_db =  DB_NAME_SHOP_PRE.$shopcode;

        return $this->dbl->dbl_conn->rawQueryOne(
            "SELECT * FROM $shop_db.customers_address WHERE customer_id = ? AND is_default_billing = TRUE",
            [$customer_id]
        );

    }
    function add_to_sales_order_payment_history($shopcode,$shopid,$order_id,$payment_method_id,$payment_method,$payment_method_name,$payment_type,$order_payment_id){

		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable
		$params = array($order_id,$payment_method_id,$payment_method,$payment_method_name,$payment_type,$order_payment_id,time(),$_SERVER['REMOTE_ADDR']);

		$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO $shop_db.sales_order_payment_history (order_id,payment_method_id,  payment_method, payment_method_name,payment_type, order_payment_id,created_at,ip) VALUES (?, ?, ?,?, ?, ?,?, ?)", $params);

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

	function getShippingChargesByQuoteWeight($shopcode,$shopid,$quote_id){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$ShippingAddress=$this->getQuoteShippingAddressById($shopcode,$quote_id,2);

		if($ShippingAddress!=false){

			$ShopData=$this->get_fbc_user_shop_details($shopid);

			if($ShopData!=false){
				$zumba_shop_flag=$ShopData['shop_flag'];
				$vat_flag = $ShopData['vat_flag'];
			}else{
				$zumba_shop_flag='';
				$vat_flag = '';
			}

			if(isset($vat_flag) && $vat_flag==1){
				$shipping_country = $ShippingAddress['country'];

				$OrderItems=$this->get_sales_quote_items($shopcode,$quote_id);
				$total_weight_arr = array();
				$total_weight = 0;
				$total_price = 0;
				foreach($OrderItems as $item){

				$product_data = $this->get_product_details($shopcode,$item['product_id']);
				$product_weight = $product_data[0]['weight']*$item['qty_ordered'];
				array_push($total_weight_arr,$product_weight);
				$total_price += $item['total_price'];
				}
				$total_weight= array_sum($total_weight_arr);

				$eu_shipping_charges = $this->getShippingChargesByCountry($shopcode,$shopid,$shipping_country,$total_weight, $total_price);
				return $eu_shipping_charges;
			}
		}
	}
}
