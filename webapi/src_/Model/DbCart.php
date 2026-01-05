<?php

use App\Application\Actions\Cart\GetCartListing;

Class DbCart{
	private $dbl;

	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}


	public function add_to_sales_quote($session_id,$customer_id='')
	{

		if($customer_id!=''){
			$params = array($session_id, $customer_id,time(),'login','0');
			$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO sales_quote (session_id, customer_id,created_at,checkout_method,customer_is_guest) VALUES (?, ?, ?, ?, ?)", $params);

		}else{
			$params = array($session_id, time(),'guest','0');
			$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO sales_quote (session_id, created_at,checkout_method,customer_is_guest) VALUES (?, ?,?, ?)", $params);
		}


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

	public function add_to_sales_quote_item($quote_id,$product_type,$product_inv_type, $product_id,$product_name,$product_code,$quantity,$sku,$barcode,$price,$total_price,$created_by='',$parent_product_id='',$product_variants='',$tax_percent='',$tax_amount='',$isPrelaunch='',$bundle_child_details='')
	{
		$created_by_type=0;

		if($parent_product_id!=''){
			$params = array($quote_id, $product_type,$product_inv_type,$product_id, $product_name,$product_code, $quantity,$sku, $barcode,$price, $total_price,$parent_product_id, $product_variants,$tax_percent,$tax_amount,time(),$created_by,$created_by_type,$isPrelaunch,$bundle_child_details,$_SERVER['REMOTE_ADDR']);
			$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO sales_quote_items (quote_id,product_type, product_inv_type, product_id, product_name,product_code, qty_ordered,sku,barcode,price,  total_price,parent_product_id,product_variants,tax_percent,tax_amount,created_at,created_by,created_by_type,prelaunch,bundle_child_details,ip) VALUES (?, ?, ?,?, ?, ?,?, ?, ?,?, ?, ?,?, ?, ?,?,?,?,?,?,?,?)", $params);

		}else{
			$params = array($quote_id, $product_type,$product_inv_type, $product_id, $product_name,$product_code, $quantity,$sku, $barcode,$price, $total_price,$tax_percent,$tax_amount,time(),$created_by,$created_by_type,$isPrelaunch,$bundle_child_details,$_SERVER['REMOTE_ADDR']);
			$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO sales_quote_items (quote_id,product_type,product_inv_type, product_id, product_name,product_code, qty_ordered,sku,barcode,price,  total_price,shop_id,tax_percent,tax_amount,created_at,created_by,created_by_type,prelaunch,bundle_child_details,ip) VALUES (?, ?, ?,?, ?, ?,?, ?, ?,?, ?,?, ?,?,?,?,?,?,?,?)", $params);

		}

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

	 public function get_product_variant_details($parent_product_id,$product_id)
	{

		$params = array($parent_product_id,$product_id);
		$result = $this->dbl->dbl_conn->rawQuery("SELECT * FROM products_variants where parent_id = ? AND product_id = ?  ORDER BY id ASC",$params);
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


	 public function getAttributeDetails($attr_id)
	 {
		 $sql =  "SELECT * FROM eav_attributes where `id` = '$attr_id' ";
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



	  public function getAttributeOptionDetails($option_id)
	 {
		 $sql =  "SELECT * FROM eav_attributes_options where `id` = '$option_id'";
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

	public function calculateQuoteTaxAmout($quote_id, $AllQuoteItems=false)
 	{
		$total_tax_amount = 0;

		if($AllQuoteItems != false) {

			if(is_array($AllQuoteItems) && count($AllQuoteItems) > 0)
			{
				$QuoteData=$this->getQuoteDataById($quote_id);
				$coupon_code=$QuoteData['coupon_code'];

				foreach($AllQuoteItems as $value)
				{

					$flag = 0;

					$ExeptionalTaxesData = $this->getExeptionalTaxesData();
					$ExeptionalCategories = $this->getExeptionalCategories();



					if(!empty($ExeptionalTaxesData) && count($ExeptionalTaxesData) > 0 ){

						if($value['product_type']=='conf-simple'){
							$ProductCategory=$this->getCategoriesForExceptional($value['product_type'],$value['product_id'],$value['parent_product_id']);
						}else{
							$ProductCategory=$this->getCategoriesForExceptional($value['product_type'],$value['product_id']);
						}

						$Productscat = explode(',',$ProductCategory['category_ids']);

						if(!empty($ExeptionalCategories) && count($ExeptionalCategories) > 0 ){
							foreach($ExeptionalCategories as $ExpcatId){
								if(in_array($ExpcatId['category_id'], $Productscat)) {
									$flag = 1;
									break;
								}else {
								}
							}
						}

					}

					if($coupon_code != "" && $value['price'] > 0.00 && $value['discount_percent'] > 0.00) {
						$pro_price_incl_tax = $value['price'] - ($value['price']*$value['discount_percent'])/100;
					} else {
						$pro_price_incl_tax = $value['price'];
					}

					$tax_amount = 0;
					$tax_amount_item = 0;


					if(!empty($ExeptionalTaxesData) && ($pro_price_incl_tax < $ExeptionalTaxesData['less_than_amount']) && $flag == 1){
						$tax_percent = $ExeptionalTaxesData['less_than_tax_percent'];

						if($tax_percent > 0.00 && $pro_price_incl_tax > 0.00) {
							$pro_price_excl_tax = $pro_price_incl_tax / ((100+$tax_percent)/100);
							$tax_amount_item =  $pro_price_incl_tax - $pro_price_excl_tax;
						}

						$this->updateTaxPercentQuote($value['quote_id'],$value['item_id'],$tax_percent,$tax_amount_item);
					}else{

						$tax_percent = $value['tax_percent'];

						if($tax_percent > 0.00 && $pro_price_incl_tax > 0.00) {
							$pro_price_excl_tax = $pro_price_incl_tax / ((100+$tax_percent)/100);
							$tax_amount_item =  $pro_price_incl_tax - $pro_price_excl_tax;

						}


						$this->updateTaxPercentQuote($value['quote_id'],$value['item_id'],$tax_percent,$tax_amount_item);
					}

					if($tax_percent > 0.00 && $pro_price_incl_tax > 0.00) {
						$pro_price_excl_tax = $pro_price_incl_tax / ((100+$tax_percent)/100);
						$tax_amount =  $pro_price_incl_tax - $pro_price_excl_tax;
						$total_tax_amount = $total_tax_amount+($tax_amount*$value['qty_ordered']);
					}
				}
			}
		}
		return $total_tax_amount;
 	}

  	public function getTotalByQuote($quote_id)
 	{
		$sql = "SELECT sum(price*qty_ordered) as total FROM sales_quote_items where `quote_id` = '$quote_id'";
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



	 public function getTotalQuantityByQuote($quote_id)
	 {
		$sql = "SELECT sum(qty_ordered) as total_qty FROM sales_quote_items where quote_id = '$quote_id'";
		$row = $this->dbl->dbl_conn->rawQueryOne($sql);
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

	 public function getQuoteDataById($quote_id)
	 {
		 $sql =  "SELECT * FROM sales_quote where `quote_id` = '$quote_id'";
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

 	public function getCouponDataByCode($shopcode,$coupon_code)
 	{
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql = "SELECT * FROM $shop_db.salesrule_coupon WHERE coupon_code = '$coupon_code'";
		 $row = $this->dbl->dbl_conn->rawQueryOne($sql);
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


		 $sql = "SELECT * FROM $shop_db.sales_quote_items where `item_id` = '$item_id'";
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

	public function updateCouponDiscount($shopcode,$shopid,$quote_id,$couponData=array(),$cartData=array(),$flag='')
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$coupon_code=$couponData['coupon_code'];


		if((isset($couponData) && count($couponData) > 0 ) &&  (isset($cartData['cartItems']) && count($cartData['cartItems']) > 0))
		{
			$cartItems = array();
			$rule_ids= '';

			$status_flag=false;

			$TotalInfo=$this->getTotalByQuote($shopcode,$quote_id);
			$base_subtotal=$TotalInfo['total'];

			$QuoteData=$this->getQuoteDataById($shopcode,$quote_id);
			$subtotal=$QuoteData['subtotal'];
			$customer_id=$QuoteData['customer_id'];

			$cartItems = (isset($cartData['cartItems']) && count($cartData['cartItems'])>0)?$cartData['cartItems']:array();
			$rule_ids = ','.$couponData['rule_id'];

			if($couponData['coupon_type'] == 1) {
				/*---------------------Voucher Start------------------------------------*/
				if($couponData['min_cart_value']!='' && ($base_subtotal < $couponData['min_cart_value']) ) {
					if($flag == 'apply_btn'){
						$status_flag=false;
					}else{
						$this->removeCouponCode($shopcode,$quote_id,$couponData['coupon_code'],$couponData['coupon_type']);
						$status_flag=true;
					}
				}else{
					//added by al
					$usge_per_coupon=$couponData['usge_per_coupon'];
					$usage_per_customer=$couponData['usage_per_customer'];

					  if($couponData['type']==4) {

							if($customer_id>0 && ($usage_per_customer!=0 && $usage_per_customer>0)) {
								$CouponUsedInOrderByCustomer=$this->getCouponUsedCountInOrders($shopcode,$couponData['coupon_code'],$couponData['coupon_type'],$customer_id);


								if($CouponUsedInOrderByCustomer!=false){

									$order_usge_per_customer=(isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer>0)?$CouponUsedInOrderByCustomer:0;
								}else{
									$order_usge_per_customer=0;
								}

								if(($order_usge_per_customer>0) && ($order_usge_per_customer>=$usage_per_customer))
								{

									$status_flag=false;
								}else{


									if($couponData['apply_type']=='by_percent'){
										$disc_amount = ($base_subtotal*($couponData['discount_amount']/100) );
										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
										$discount_percent = $couponData['discount_amount'];

										//$update_cart_item = $this->updateCartItemsCouponDiscount($shopcode,$quote_id,$rule_ids,$discount_percent,$cartItems);
										$status_flag=true;
									}
									else if($couponData['apply_type']=='by_fixed')
									{
										$disc_amount =$couponData['discount_amount'];
										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
										$discount_percent = 0;
										$status_flag=true;
									}


								}

							}else{
								//echo "666666666666<br>";exit;

								if($couponData['apply_type']=='by_percent'){
									$disc_amount = ($base_subtotal*($couponData['discount_amount']/100) );
									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
									$discount_percent = $couponData['discount_amount'];

									//$update_cart_item = $this->updateCartItemsCouponDiscount($shopcode,$quote_id,$rule_ids,$discount_percent,$cartItems);
									$status_flag=true;
								}
								else if($couponData['apply_type']=='by_fixed')
								{
									$disc_amount =$couponData['discount_amount'];
									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
									$discount_percent = 0;
									$status_flag=true;
								}



							}


					}
					else if($couponData['type']==3 && $couponData['usge_per_coupon']!=0 && $couponData['usge_per_coupon']>0) {


						$CouponUsedInOrder=$this->getCouponUsedCountInOrders($shopcode,$couponData['coupon_code'],$couponData['coupon_type']);


						if($usge_per_coupon!=0 || $usge_per_coupon>0) {

							if($CouponUsedInOrder!=false){
								$order_usge_per_coupon=(isset($CouponUsedInOrder) && $CouponUsedInOrder>0)?$CouponUsedInOrder:0;
							}else{
								$order_usge_per_coupon=0;
							}

							if(($order_usge_per_coupon>0) && ($order_usge_per_coupon>=$usge_per_coupon))
							{

								$status_flag=false;
							}else{

								 //echo "1111111111<br>";exit;

								if($customer_id>0 && ($usage_per_customer!=0 && $usage_per_customer>0)){
									$CouponUsedInOrderByCustomer=$this->getCouponUsedCountInOrders($shopcode,$couponData['coupon_code'],$couponData['coupon_type'],$customer_id);

									//echo $CouponUsedInOrderByCustomer.'=========='.$usage_per_customer.'<br>';exit;


									if($CouponUsedInOrderByCustomer!=false){
									 // echo "33333333<br>";exit;
										$order_usge_per_customer=(isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer>0)?$CouponUsedInOrderByCustomer:0;
									}else{
										$order_usge_per_customer=0;
									}

									if(($order_usge_per_customer>0) && ($order_usge_per_customer>=$usage_per_customer))
									{
										// echo "4444444444444444<br>";exit;
										$status_flag=false;
									}else{
										// echo "5555555555<br>";exit;

										if($couponData['apply_type']=='by_percent'){
											$disc_amount = ($base_subtotal*($couponData['discount_amount']/100) );
											$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
											$discount_percent = $couponData['discount_amount'];

											//$update_cart_item = $this->updateCartItemsCouponDiscount($shopcode,$quote_id,$rule_ids,$discount_percent,$cartItems);
											$status_flag=true;
										}
										else if($couponData['apply_type']=='by_fixed')
										{
											$disc_amount =$couponData['discount_amount'];
											$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
											$discount_percent = 0;
											$status_flag=true;
										}


									}

								}else{
									//echo "666666666666<br>";exit;

									if($couponData['apply_type']=='by_percent'){
										$disc_amount = ($base_subtotal*($couponData['discount_amount']/100) );
										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
										$discount_percent = $couponData['discount_amount'];

										//$update_cart_item = $this->updateCartItemsCouponDiscount($shopcode,$quote_id,$rule_ids,$discount_percent,$cartItems);
										$status_flag=true;
									}
									else if($couponData['apply_type']=='by_fixed')
									{
										$disc_amount =$couponData['discount_amount'];
										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
										$discount_percent = 0;
										$status_flag=true;
									}



								}
							}

						}
					}
					else  if(($couponData['type']==3) && ($couponData['usge_per_coupon']==0) && ($usage_per_customer!=0 && $usage_per_customer>0)) {

								 //echo "1111111----------111<br>";exit;

								if($customer_id>0 && ($usage_per_customer!=0 && $usage_per_customer>0)) {
									$CouponUsedInOrderByCustomer=$this->getCouponUsedCountInOrders($shopcode,$couponData['coupon_code'],$couponData['coupon_type'],$customer_id);

									//echo $CouponUsedInOrderByCustomer.'=========='.$usage_per_customer.'<br>';exit;


									if($CouponUsedInOrderByCustomer!=false){
									 // echo "33333333<br>";exit;
										$order_usge_per_customer=(isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer>0)?$CouponUsedInOrderByCustomer:0;
									}else{
										$order_usge_per_customer=0;
									}

									if(($order_usge_per_customer>0) && ($order_usge_per_customer>=$usage_per_customer))
									{
										// echo "4444444444444444<br>";exit;
										$status_flag=false;
									}else{
										// echo "5555555555<br>";exit;

										if($couponData['apply_type']=='by_percent'){
											$disc_amount = ($base_subtotal*($couponData['discount_amount']/100) );
											$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
											$discount_percent = $couponData['discount_amount'];

											//$update_cart_item = $this->updateCartItemsCouponDiscount($shopcode,$quote_id,$rule_ids,$discount_percent,$cartItems);
											$status_flag=true;
										}
										else if($couponData['apply_type']=='by_fixed')
										{
											$disc_amount =$couponData['discount_amount'];
											$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
											$discount_percent = 0;
											$status_flag=true;
										}


									}

								}else{
									//echo "666666666666<br>";exit;

									if($couponData['apply_type']=='by_percent'){
										$disc_amount = ($base_subtotal*($couponData['discount_amount']/100) );
										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
										$discount_percent = $couponData['discount_amount'];

										//$update_cart_item = $this->updateCartItemsCouponDiscount($shopcode,$quote_id,$rule_ids,$discount_percent,$cartItems);
										$status_flag=true;
									}
									else if($couponData['apply_type']=='by_fixed')
									{
										$disc_amount =$couponData['discount_amount'];
										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
										$discount_percent = 0;
										$status_flag=true;
									}


								}

					}
					else {
						if($couponData['apply_type']=='by_percent'){
							$disc_amount = ($subtotal*($couponData['discount_amount']/100) );
							$discount_amount = ($disc_amount > $subtotal) ? $subtotal : $disc_amount;
							$discount_percent = $couponData['discount_amount'];

							//$update_cart_item = $this->updateCartItemsCouponDiscount($shopcode,$quote_id,$rule_ids,$discount_percent,$cartItems);
							$status_flag=true;
						}
						else if($couponData['apply_type']=='by_fixed')
						{
							$disc_amount =$couponData['discount_amount'];
							$discount_amount = ($disc_amount > $subtotal) ? $subtotal : $disc_amount;
							$discount_percent = 0;
							$status_flag=true;
						}


					}
				}
				/*---------------------Voucher End------------------------------------*/

			}else if($couponData['coupon_type'] == 0) {
				if($couponData['type'] == 1){
					/*----------Catalog discount by AL :start------------------------------------*/
					$discount_percent=$couponData['discount_amount'];
					$discount_amount=0.00;

					$ApplyOnCategory=$couponData['apply_on_categories'];
					if( strpos($ApplyOnCategory, ',') !== false ) {
						$CategoryArr=explode(',',$ApplyOnCategory);
						$CategoryArr=array_filter(array_unique($CategoryArr));
					}else{
						$CategoryArr[]=$ApplyOnCategory;
					}

					if(is_array($cartItems) && count($cartItems) > 0)
					{
						foreach($cartItems as $value)
						{
							if($value['product_type']=='conf-simple'){
								$ProductCategory=$this->getProductCategory($shopcode,$shopid,$value['product_type'],$value['product_id'],$value['parent_product_id']);
							}else{
								$ProductCategory=$this->getProductCategory($shopcode,$shopid,$value['product_type'],$value['product_id']);
							}


							if($ProductCategory!=false){

								if( strpos($ApplyOnCategory, ',') !== false ) {
									$CategoryArr=explode(',',$ApplyOnCategory);
									$CategoryArr=array_filter(array_unique($CategoryArr));
								}else{
									$CategoryArr[]=$ApplyOnCategory;
								}
								$ProductCategoryArr=explode(',',$ProductCategory['category_ids']);

								$IsCategoryExist = !empty(array_intersect($ProductCategoryArr, $CategoryArr));

								if($IsCategoryExist==1){

									$item_id = $value['item_id'];
									$qty = $value['qty_ordered'];

									$perItemDiscount = $value['price']*($discount_percent / 100);
									$total_discount_amount = $perItemDiscount*$qty;

									$discount_amount=$discount_amount+$total_discount_amount;

									$params=array($rule_ids,$discount_percent,$perItemDiscount,$total_discount_amount,time(),$item_id,$quote_id);
									$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ?,updated_at=? WHERE item_id = ? AND quote_id= ?",$params);

									if ($this->dbl->dbl_conn->getLastErrno() === 0){
										$status_flag=true;
									}
								}
							}
						}
						//return $apply_flag;
					}else{
						//return $apply_flag;
					}

				/*------------------------------------End------------------------------------*/

				}else if($couponData['type'] == 2){

					/*----------Product discount by AL :start------------------------------------*/

					$discount_percent=$couponData['discount_amount'];
					$discount_amount=0.00;

					$ApplyOnProduct=$couponData['apply_on_products'];
					if( strpos($ApplyOnProduct, ',') !== false ) {
						$ProductArr=explode(',',$ApplyOnProduct);
						$ProductArr=array_filter(array_unique($ProductArr));
					}else{
						$ProductArr[]=$ApplyOnProduct;
					}



					if(is_array($cartItems) && count($cartItems) > 0)
					{
						foreach($cartItems as $value)
						{
								if(is_array($ProductArr) && in_array($value['product_id'],$ProductArr)){

									$item_id = $value['item_id'];
									$qty = $value['qty_ordered'];

									$perItemDiscount = $value['price']*($discount_percent / 100);
									$total_discount_amount = $perItemDiscount*$qty;

									$discount_amount=$discount_amount+$total_discount_amount;

									$params=array($rule_ids,$discount_percent,$perItemDiscount,$total_discount_amount,time(),$item_id,$quote_id);
									$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ?,updated_at=? WHERE item_id = ? AND quote_id= ?",$params);

									if ($this->dbl->dbl_conn->getLastErrno() === 0){
										$status_flag=true;
									}else{
										$status_flag=false;
									}
								}
						}

					}else{
						$status_flag=false;
					}

				/*------------------------------------End------------------------------------*/


				}else if($couponData['type'] == 3 || $couponData['type'] == 4){


					$usge_per_coupon=$couponData['usge_per_coupon'];
					$usage_per_customer=$couponData['usage_per_customer'];



						if($couponData['apply_condition'] == 'discount_on_mincartval'){

							if($couponData['min_cart_value']!='' && ($base_subtotal < $couponData['min_cart_value']) ) {
								if($flag == 'apply_btn'){
									$status_flag=false;
								}else{
									$this->removeCouponCode($shopcode,$quote_id,$couponData['coupon_code'],$couponData['coupon_type']);
									$status_flag=true;
								}
							} else if($couponData['type']==4) {

								if($customer_id>0 && ($usage_per_customer!=0 && $usage_per_customer>0)) {
									$CouponUsedInOrderByCustomer=$this->getCouponUsedCountInOrders($shopcode,$couponData['coupon_code'],$couponData['coupon_type'],$customer_id);


									if($CouponUsedInOrderByCustomer!=false){

										$order_usge_per_customer=(isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer>0)?$CouponUsedInOrderByCustomer:0;
									}else{
										$order_usge_per_customer=0;
									}

									if(($order_usge_per_customer>0) && ($order_usge_per_customer>=$usage_per_customer))
									{

										$status_flag=false;
									}else{


										if($couponData['apply_type']=='by_percent'){
											$disc_amount = ($base_subtotal*($couponData['discount_amount']/100) );
											$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
											$discount_percent = $couponData['discount_amount'];

											$update_cart_item = $this->updateCartItemsCouponDiscount($shopcode,$quote_id,$rule_ids,$discount_percent,$cartItems);
											$status_flag=true;
										}
										else if($couponData['apply_type']=='by_fixed')
										{
											$disc_amount =$couponData['discount_amount'];
											$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
											$discount_percent = 0;
											$status_flag=true;
										}


									}

								}else{
									//echo "666666666666<br>";exit;

									if($couponData['apply_type']=='by_percent'){
										$disc_amount = ($base_subtotal*($couponData['discount_amount']/100) );
										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
										$discount_percent = $couponData['discount_amount'];

										$update_cart_item = $this->updateCartItemsCouponDiscount($shopcode,$quote_id,$rule_ids,$discount_percent,$cartItems);
										$status_flag=true;
									}
									else if($couponData['apply_type']=='by_fixed')
									{
										$disc_amount =$couponData['discount_amount'];
										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
										$discount_percent = 0;
										$status_flag=true;
									}



								}


							}
							else if($couponData['type']==3 && $couponData['usge_per_coupon']!=0 && $couponData['usge_per_coupon']>0){


								$CouponUsedInOrder=$this->getCouponUsedCountInOrders($shopcode,$couponData['coupon_code'],$couponData['coupon_type']);


								if($usge_per_coupon!=0 || $usge_per_coupon>0){

									if($CouponUsedInOrder!=false){
										$order_usge_per_coupon=(isset($CouponUsedInOrder) && $CouponUsedInOrder>0)?$CouponUsedInOrder:0;
									}else{
										$order_usge_per_coupon=0;
									}

									if(($order_usge_per_coupon>0) && ($order_usge_per_coupon>=$usge_per_coupon))
									{

										$status_flag=false;
									}else{

										 //echo "1111111111<br>";exit;

										if($customer_id>0 && ($usage_per_customer!=0 && $usage_per_customer>0)){
											$CouponUsedInOrderByCustomer=$this->getCouponUsedCountInOrders($shopcode,$couponData['coupon_code'],$couponData['coupon_type'],$customer_id);

											//echo $CouponUsedInOrderByCustomer.'=========='.$usage_per_customer.'<br>';exit;


											if($CouponUsedInOrderByCustomer!=false){
											 // echo "33333333<br>";exit;
												$order_usge_per_customer=(isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer>0)?$CouponUsedInOrderByCustomer:0;
											}else{
												$order_usge_per_customer=0;
											}

											if(($order_usge_per_customer>0) && ($order_usge_per_customer>=$usage_per_customer))
											{
												// echo "4444444444444444<br>";exit;
												$status_flag=false;
											}else{
												// echo "5555555555<br>";exit;

												if($couponData['apply_type']=='by_percent'){
													$disc_amount = ($base_subtotal*($couponData['discount_amount']/100) );
													$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
													$discount_percent = $couponData['discount_amount'];

													$update_cart_item = $this->updateCartItemsCouponDiscount($shopcode,$quote_id,$rule_ids,$discount_percent,$cartItems);
													$status_flag=true;
												}
												else if($couponData['apply_type']=='by_fixed')
												{
													$disc_amount =$couponData['discount_amount'];
													$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
													$discount_percent = 0;
													$status_flag=true;
												}


											}

										}else{
											//echo "666666666666<br>";exit;

											if($couponData['apply_type']=='by_percent'){
												$disc_amount = ($base_subtotal*($couponData['discount_amount']/100) );
												$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
												$discount_percent = $couponData['discount_amount'];

												$update_cart_item = $this->updateCartItemsCouponDiscount($shopcode,$quote_id,$rule_ids,$discount_percent,$cartItems);
												$status_flag=true;
											}
											else if($couponData['apply_type']=='by_fixed')
											{
												$disc_amount =$couponData['discount_amount'];
												$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
												$discount_percent = 0;
												$status_flag=true;
											}



										}
									}

								}
							}
							else  if(($couponData['type']==3) && ($couponData['usge_per_coupon']==0) && ($usage_per_customer!=0 && $usage_per_customer>0)){

										 //echo "1111111----------111<br>";exit;

										if($customer_id>0 && ($usage_per_customer!=0 && $usage_per_customer>0)){
											$CouponUsedInOrderByCustomer=$this->getCouponUsedCountInOrders($shopcode,$couponData['coupon_code'],$couponData['coupon_type'],$customer_id);

											//echo $CouponUsedInOrderByCustomer.'=========='.$usage_per_customer.'<br>';exit;


											if($CouponUsedInOrderByCustomer!=false){
											 // echo "33333333<br>";exit;
												$order_usge_per_customer=(isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer>0)?$CouponUsedInOrderByCustomer:0;
											}else{
												$order_usge_per_customer=0;
											}

											if(($order_usge_per_customer>0) && ($order_usge_per_customer>=$usage_per_customer))
											{
												// echo "4444444444444444<br>";exit;
												$status_flag=false;
											}else{
												// echo "5555555555<br>";exit;

												if($couponData['apply_type']=='by_percent'){
													$disc_amount = ($base_subtotal*($couponData['discount_amount']/100) );
													$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
													$discount_percent = $couponData['discount_amount'];

													$update_cart_item = $this->updateCartItemsCouponDiscount($shopcode,$quote_id,$rule_ids,$discount_percent,$cartItems);
													$status_flag=true;
												}
												else if($couponData['apply_type']=='by_fixed')
												{
													$disc_amount =$couponData['discount_amount'];
													$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
													$discount_percent = 0;
													$status_flag=true;
												}


											}

										}else{
											//echo "666666666666<br>";exit;

											if($couponData['apply_type']=='by_percent'){
												$disc_amount = ($base_subtotal*($couponData['discount_amount']/100) );
												$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
												$discount_percent = $couponData['discount_amount'];

												$update_cart_item = $this->updateCartItemsCouponDiscount($shopcode,$quote_id,$rule_ids,$discount_percent,$cartItems);
												$status_flag=true;
											}
											else if($couponData['apply_type']=='by_fixed')
											{
												$disc_amount =$couponData['discount_amount'];
												$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
												$discount_percent = 0;
												$status_flag=true;
											}


										}

							}
							else{

								/*--------final else--------------------------------------------*/


								if($couponData['apply_type']=='by_percent'){
									$disc_amount = ($base_subtotal*($couponData['discount_amount']/100) );
									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
									$discount_percent = $couponData['discount_amount'];

									$update_cart_item = $this->updateCartItemsCouponDiscount($shopcode,$quote_id,$rule_ids,$discount_percent,$cartItems);
									$status_flag=true;
								}
								else if($couponData['apply_type']=='by_fixed')
								{
									$disc_amount =$couponData['discount_amount'];
									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;
									$discount_percent = 0;
									$status_flag=true;
								}

								/*-----------------Final else----------------------*/

							}
						}



					/*---------------------------------------------*/
				}
			}


			if($status_flag==true){
				$update_quote = $this->UpateQuoteCouponDiscount($shopcode,$quote_id,$rule_ids,$couponData['coupon_code'],$discount_amount,$discount_percent,$couponData['coupon_type']);
				return true;
			}else{
				return false;
			}

		}else{
			return false;
		}
	}

	public function addCouponDiscount($shopcode,$shopid,$QuoteData)
	{
		if($QuoteData['coupon_code']!=''){
			$coupon_type = 0;

			$couponData = $this->getCouponData($shopcode,$QuoteData['coupon_code'],$coupon_type,$QuoteData['customer_id']);
			$cartData = $this->getCartListing($shopcode,$QuoteData['session_id']);

			$this->updateCouponDiscount($shopcode,$shopid,$QuoteData['quote_id'],$couponData,$cartData);
		}

		if($QuoteData['voucher_code']!=''){
			$coupon_type = 1;

			$couponData = $this->getCouponData($shopcode,$QuoteData['voucher_code'],$coupon_type,$QuoteData['customer_id']);
			$cartData = $this->getCartListing($shopcode,$QuoteData['session_id']);
			$this->updateCouponDiscount($shopcode,$shopid,$QuoteData['quote_id'],$couponData,$cartData);
		}
	}

 	public function UpateQuoteTotal($quote_id,$remove_coupon='')
	{

		$AllQuoteItems=$this->getQuoteItems($quote_id);

		$QuoteData=$this->getQuoteDataById($quote_id);

		if($AllQuoteItems==false || $remove_coupon==1){

			$discount_amount=0.00;
			$voucher_amount=0.00;
			$applied_rule_ids='';
			$base_discount_amount=0.00;
			$discount_amount=0.00;
			$coupon_code='';

			$params = array($base_discount_amount,$discount_amount,$applied_rule_ids, time(),$quote_id);

			$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET base_discount_amount=?, discount_amount=?, applied_rule_ids=?,  updated_at = ?  WHERE quote_id=?",$params);
			// $params_items=array($rule_ids,0.00,0.00,0.00,$quote_id);
			$params_items=array($applied_rule_ids,0.00,0.00,0.00,$quote_id);
			$update_row_sales_item = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ? WHERE quote_id= ?",$params_items);

		}else{
			$discount_amount=$QuoteData['discount_amount'];
			$voucher_amount=$QuoteData['voucher_amount'];

			$coupon_code=$QuoteData['coupon_code'];
		}

		$shipping_amount=$QuoteData['shipping_amount'];


		$TotalInfo=$this->getTotalByQuote($quote_id);
		$subtotal=$TotalInfo['total'];
		$base_subtotal=$TotalInfo['total'];

		$TotalQtyInfo=$this->getTotalQuantityByQuote($quote_id);
		$total_qty=(isset($TotalQtyInfo['total_qty']) && $TotalQtyInfo['total_qty']>0)?$TotalQtyInfo['total_qty']:0;

		if($discount_amount>0){
			$subtotal=$base_subtotal-$discount_amount;
		}

		if($subtotal <= 0){
			$subtotal = 0;
		}

		$subtotal=$subtotal+$shipping_amount;

		if($voucher_amount>0){
			if($voucher_amount>=$subtotal){
				$grand_total=0;
			}else{
				$grand_total=$subtotal-$voucher_amount;
			}
		}else{
			$grand_total=$subtotal;
		}

		if($grand_total <= 0){
			$grand_total = 0;
		}

		$QuoteTotalTaxamount = $this->calculateQuoteTaxAmout($quote_id,$AllQuoteItems);
		$tax_amount = $QuoteTotalTaxamount;

		$params = array($total_qty, $base_subtotal, $subtotal, $grand_total, $grand_total, $tax_amount, $tax_amount,$coupon_code, time(),$quote_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET total_qty_ordered=?, base_subtotal=?, subtotal=?, base_grand_total=?, grand_total=?, base_tax_amount=?, tax_amount=?,coupon_code=?, updated_at = ?  WHERE quote_id=?",$params);
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

	public function getCartListing($shopcode,$session_id,$quote_id = '',$customer_id = '',$lang_code = '')
	{
		return (new GetCartListing())($shopcode, $session_id, $quote_id, $customer_id, $lang_code);
	}

	function getCartCountByQuoteId($shopcode,$quote_id,$customer_id=''){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		if(empty($customer_id)){

			 $params = array($quote_id);
			 $row  = $this->dbl->dbl_conn->rawQueryOne("SELECT count(sqi.item_id) as total_count FROM $shop_db.sales_quote_items  as sqi LEFT JOIN $shop_db.sales_quote as sq ON sqi.quote_id = sq.quote_id where sq.quote_id =  ?  ",$params);
		}else{

			$params = array($quote_id,$customer_id);
			$row  = $this->dbl->dbl_conn->rawQueryOne("SELECT count(sqi.item_id) as total_count FROM $shop_db.sales_quote_items as sqi LEFT JOIN $shop_db.sales_quote as sq ON sqi.quote_id = sq.quote_id where (sq.quote_id = ?  AND sq.customer_id= ? )",$params);
		}

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

	function getCartCountBySessionId($shopcode,$session_id,$customer_id=''){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		if(empty($customer_id)){

			 $params = array($session_id);
			 $row  = $this->dbl->dbl_conn->rawQueryOne("SELECT count(sqi.item_id) as total_count FROM $shop_db.sales_quote_items as sqi LEFT JOIN $shop_db.sales_quote as sq ON sqi.quote_id = sq.quote_id where sq.session_id =  ?  ",$params);
		}else{

			$params = array($session_id,$customer_id);
			$row  = $this->dbl->dbl_conn->rawQueryOne("SELECT count(sqi.item_id) as total_count FROM $shop_db.sales_quote_items as sqi LEFT JOIN $shop_db.sales_quote as sq ON sqi.quote_id = sq.quote_id where (sq.session_id = ?  AND sq.customer_id= ? )",$params);
		}
		//$row  = $this->dbl->dbl_conn->rawQueryOne($sql);
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

	function removeCartItem($quote_id,$item_id){


		$params=array($quote_id,$item_id);
		$delete_row = $this->dbl->dbl_conn->rawQueryOne("DELETE FROM sales_quote_items where quote_id= ? AND item_id = ?",$params);

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

	function updateCartItem($shopcode,$item_id,$quote_id,$qty){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$itemData = $this->getQuoteItemDataById($shopcode,$item_id);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				$price = $itemData['price'];
				$tax_percent = $itemData['tax_percent'];
				$tax_amount = $itemData['tax_amount'];

				$totalPrice = $price * $qty;
				$params=array($qty,$totalPrice,$tax_percent,$tax_amount,time(),$item_id,$quote_id);
				$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.sales_quote_items SET qty_ordered = ?, total_price = ?, tax_percent = ?, tax_amount = ?, updated_at = ? WHERE item_id = ? AND quote_id= ?",$params);

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
			}else{
				return false;
			}
		} else {
			return false;
		}
	}
	function updateWholeCartItems($shopcode,$quote_id,$cart_items){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		$items = json_decode($cart_items);
		if(is_array($items) && count($items) > 0){
			foreach($items as $value){
				$item_id = $value->item_id;
				$qty = $value->qty;
				$itemData = $this->getQuoteItemDataById($shopcode,$item_id);
				if ($this->dbl->dbl_conn->getLastErrno() === 0){
					if ($this->dbl->dbl_conn->count > 0){
						$price = $itemData['price'];
						$totalPrice = $price * $qty;
						$tax_percent = $itemData['tax_percent'];
						$tax_amount = $itemData['tax_amount'];

						$params=array($qty,$totalPrice,$tax_percent,$tax_amount,time(),$item_id,$quote_id);
						$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.sales_quote_items SET qty_ordered = ?, total_price = ?, tax_percent = ?, tax_amount = ?, updated_at = ? WHERE item_id = ? AND quote_id= ?",$params);

						if ($this->dbl->dbl_conn->getLastErrno() === 0){
							$flag=true;
						} else {
							$flag=false;
						}
					}else{
						$flag=false;
					}
				} else {
					$flag=false;
				}
			}
			return $flag;
		}else{
			return false;
		}
	}

	 public function checkQuoteItemDataExistById($shopcode,$quote_id, $product_id,$parent_product_id='')
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		if(isset($parent_product_id) && $parent_product_id!=''){
			$sql =  "SELECT * FROM $shop_db.sales_quote_items where `product_id` = '$product_id' AND parent_product_id='$parent_product_id'  AND quote_id = $quote_id ";
		}else{
			 $sql =  "SELECT * FROM $shop_db.sales_quote_items where `product_id` = '$product_id' AND quote_id = $quote_id ";
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

	 public function checkQuoteItemDataExistInOtherBundle($shopcode,$quote_id,$bundle_product_id='',$product_id='',$parent_product_id='')
	 {
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$parent_product_id = ($parent_product_id !='')?$parent_product_id:0;

		$where_bundle_child = '"parent_id":'.$parent_product_id.',"product_id":'.$product_id;
		$params=array($bundle_product_id,$quote_id);

		$add_query = '';
		if($bundle_product_id != ''){
			$add_query = "AND product_id != $bundle_product_id";
		}

		$sql = "SELECT product_id FROM $shop_db.sales_quote_items where product_type = 'bundle' ".$add_query." AND bundle_child_details like '%".$where_bundle_child."%' AND quote_id =  ".$quote_id;
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

	public function updateCartItemQty($shopcode,$quote_id,$item_id,$qty_ordered,$total_price,$tax_percent,$tax_amount){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$params=array($qty_ordered,$total_price,$tax_percent,$tax_amount,time(),$quote_id,$item_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  $shop_db.sales_quote_items set qty_ordered = ?, total_price = ?, tax_percent = ?, tax_amount = ?, updated_at = ?  where quote_id= ? AND item_id = ? ",$params);

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

	public function getCouponData($shopcode,$coupon_code,$coupon_type='',$customer_id='')
	{
		$shop_db = DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$type='';
		$customer_email_address='';

		if(!empty($customer_id) && $customer_id != 0){

            if($coupon_type !=""){
			    $param_one=array($coupon_code,$coupon_type);
			    $query_one = "SELECT s.*, sc.coupon_id, sc.coupon_code, sc.email_address FROM $shop_db.salesrule as s INNER JOIN $shop_db.salesrule_coupon as sc ON sc.rule_id = s.rule_id   WHERE sc.coupon_code = ? AND s.coupon_type = ?   AND s.remove_flag = 0 AND s.status = 1 limit 1 ";
            }else{
                $param_one=array($coupon_code);
			    $query_one = "SELECT s.*, sc.coupon_id, sc.coupon_code, sc.email_address FROM $shop_db.salesrule as s INNER JOIN $shop_db.salesrule_coupon as sc ON sc.rule_id = s.rule_id   WHERE sc.coupon_code = ? AND s.remove_flag = 0 AND s.status = 1 limit 1 ";
            }

			$coupon_row = $this->dbl->dbl_conn->rawQueryOne($query_one,$param_one);
			//var_dump($coupon_row);exit;
			if ($this->dbl->dbl_conn->getLastErrno() === 0){
				if ($this->dbl->dbl_conn->count > 0){

					$type=$coupon_row['type'];
				}else{

					//return false;
				}
			}else{

				//return false;
			}

			if((isset($type) && $type==4) && (isset($customer_id) && $customer_id>0)){
				$CustomerInfo=$this->getCustomerDataById($shopcode,$customer_id);
				$customer_email_address=$CustomerInfo['email_id'];
			}

		}

		$customer_inner_join = '';
		$where_cust_type = '';
		if(!empty($customer_id) && $customer_id != 0){
			//$customer_inner_join = "INNER JOIN ".$shop_db.".customers as c ON c.id = '".$customer_id."' AND FIND_IN_SET(c.customer_type_id, s.apply_to)";
			if(isset($type) && ($type==4) && ($customer_email_address!='')){
				$where_cust_type = " AND sc.email_address='$customer_email_address ' ";
			}
		}else{
			$where_cust_type = " AND FIND_IN_SET('1', s.apply_to)";
		}


        if($coupon_type !=""){
			$param = array($coupon_code,$coupon_type,0,1);
		    $query = "SELECT s.*, sc.coupon_id, sc.coupon_code FROM $shop_db.salesrule as s INNER JOIN $shop_db.salesrule_coupon as sc ON sc.rule_id = s.rule_id $customer_inner_join WHERE sc.coupon_code = ? AND s.coupon_type = ? $where_cust_type  AND s.remove_flag = ? AND s.status = ?";
        }else{
            $param = array($coupon_code,0,1);
		    $query = "SELECT s.*, sc.coupon_id, sc.coupon_code FROM $shop_db.salesrule as s INNER JOIN $shop_db.salesrule_coupon as sc ON sc.rule_id = s.rule_id $customer_inner_join WHERE sc.coupon_code = ? $where_cust_type  AND s.remove_flag = ? AND s.status = ?";
        }

		//echo $query;exit;

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

 	public function removeCouponCode($shopcode,$quote_id,$coupon_code,$coupon_type)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$QuoteData = $this->getQuoteDataById($shopcode,$quote_id);
		$applied_rule_ids = explode(",", $QuoteData['applied_rule_ids']);

		$applied_rule_arr = array_filter($applied_rule_ids);

		$CouponData = $this->getCouponDataByCode($shopcode,$coupon_code);
		$to_remove = array($CouponData['rule_id']);

		$result = array_diff($applied_rule_arr, $to_remove);
		$rule_id = '';
		if(!empty($result)){
			$rule_id = ",".implode(",", $result);
		}

		if($coupon_type == 1){
			$voucher_code = '';
			$voucher_amount = 0.00;

			$params=array($rule_id,$voucher_code,$voucher_amount,$quote_id);
			$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.sales_quote SET applied_rule_ids = ?, voucher_code = ?, voucher_amount = ? WHERE quote_id= ? ",$params);
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
		}else{
			$rule_ids = '';
			$couponcode = '';

			$params=array($rule_ids,0.00,0.00,0.00,$quote_id);
			$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ? WHERE quote_id= ?",$params);

			if ($this->dbl->dbl_conn->getLastErrno() === 0){
				$flag=true;
				if ($this->dbl->dbl_conn->count > 0){

					$params=array($rule_id,$couponcode,0.00,0.00,0.00,$quote_id);
  					$updtrow = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.sales_quote SET applied_rule_ids = ?, coupon_code = ?, base_discount_amount = ?, discount_amount = ?, discount_percent = ?  WHERE quote_id= ? ",$params);

					return $flag;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

	}

 	public function UpateQuoteCouponDiscount($shopcode, $quote_id, $rule_ids, $coupon_code, $discount_amount, $discount_percent, $coupon_type)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$QuoteData=$this->getQuoteDataById($shopcode,$quote_id);
		$applied_rule_ids = $QuoteData['applied_rule_ids'];


		$rule_ids = $applied_rule_ids.$rule_ids;

		if($coupon_type == 1){
  			$params=array($rule_ids,$coupon_code,$discount_amount,$quote_id);
  			$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.sales_quote SET applied_rule_ids = ?, voucher_code = ?, voucher_amount = ? WHERE quote_id= ? ",$params);
  		}else{
  			$params=array($rule_ids,$coupon_code,$discount_amount,$discount_amount,$discount_percent,$quote_id);
  			$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.sales_quote SET applied_rule_ids = ?, coupon_code = ?, base_discount_amount = ?, discount_amount = ?, discount_percent = ?  WHERE quote_id= ? ",$params);
  		}

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

	public function updateCartItemsCouponDiscount($shopcode, $quote_id, $rule_ids, $discount_percent, $cartItems)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		if(is_array($cartItems) && count($cartItems) > 0)
		{
			foreach($cartItems as $value)
			{
				$item_id = $value['item_id'];
				$qty = $value['qty_ordered'];

				$perItemDiscount = $value['price']*($discount_percent / 100);
				$total_discount_amount = $perItemDiscount*$qty;

				$params=array($rule_ids,$discount_percent,$perItemDiscount,$total_discount_amount,$item_id,$quote_id);
				$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ? WHERE item_id = ? AND quote_id= ?",$params);

				if ($this->dbl->dbl_conn->getLastErrno() === 0){
					$flag=true;
				}else{
					$flag=false;
				}
			}
			return $flag;
		}else{
			return false;
		}
	}

	function getProductCategory($shopcode,$shopid,$product_type,$product_id,$parent_product_id=''){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		if($product_id!=''){

			if($product_type=='conf-simple'){
				$sql = "SELECT GROUP_CONCAT(category_ids) as category_ids FROM $shop_db.products_category where product_id = $parent_product_id ";
			}else{
				$sql = "SELECT GROUP_CONCAT(category_ids) as category_ids FROM $shop_db.products_category where product_id = $product_id ";
			}
			$row = $this->dbl->dbl_conn->rawQueryOne($sql);


			if ($this->dbl->dbl_conn->getLastErrno() === 0){
				if ($this->dbl->dbl_conn->count > 0){
					return $row;
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

	function getCustomerInfo($shopcode,$shopid,$customer_id=''){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		if($customer_id!='' && $customer_id>0){

		$sql = "SELECT * FROM $shop_db.customers where id = $customer_id ";

			$row = $this->dbl->dbl_conn->rawQueryOne($sql);


			if ($this->dbl->dbl_conn->getLastErrno() === 0){
				if ($this->dbl->dbl_conn->count > 0){
					return $row;
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

	function getQuoteItems($quote_id){


		if($quote_id!='' && $quote_id>0) {

		$sql = "SELECT * FROM sales_quote_items where quote_id = $quote_id ";

			$row = $this->dbl->dbl_conn->rawQuery($sql);


			if ($this->dbl->dbl_conn->getLastErrno() === 0){
				if ($this->dbl->dbl_conn->count > 0){
					return $row;
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



	  public function getCouponUsedCountInOrders($shopcode,$coupon_code,$coupon_type,$customer_id='')
	 {
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		if($customer_id!='' && $customer_id>0){
			if($coupon_type==0){
				$sql =  "SELECT count(*) used_count FROM $shop_db.sales_order where `coupon_code` = '$coupon_code'  AND customer_id=$customer_id AND ( (status <> 7) AND ((status <> 3) OR cancel_by_customer != 0)) ";
			}else if($coupon_type==1){
				$sql =  "SELECT count(*) used_count FROM $shop_db.sales_order where `voucher_code` = '$coupon_code'  AND customer_id=$customer_id  AND ( (status <> 7) AND ((status <> 3) OR  cancel_by_customer != 0))";
			}
		}else{
			if($coupon_type==0){
				$sql =  "SELECT count(*) used_count FROM $shop_db.sales_order where `coupon_code` = '$coupon_code' AND ( (status <> 7) AND ((status <> 3) OR  cancel_by_customer != 0))";
			}else if($coupon_type==1){
				$sql =  "SELECT count(*) used_count FROM $shop_db.sales_order where `voucher_code` = '$coupon_code' AND ( (status <> 7) AND ((status <> 3) OR  cancel_by_customer != 0))";
			}
		}

		//echo $sql;exit;

		$row  = $this->dbl->dbl_conn->rawQueryOne($sql);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $row['used_count'];
			}else{
				return false;
			}
		}else{
			return false;
		}

	 }


  	public function getCustomerDataById($shopcode,$customer_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$sql = "SELECT * FROM $shop_db.customers where `id` = '$customer_id'";
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

	public function updateCartItems($quote_id,$item_id,$price,$total_price)
	{

		$params=array($price,$total_price,$quote_id,$item_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET price = ?, total_price = ? WHERE quote_id= ? AND item_id = ? ",$params);

		if($this->dbl->dbl_conn->getLastErrno() === 0){
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


	public function getExeptionalTaxesData(){

		$sql = "SELECT * FROM  `exceptional_taxes_set`";
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

	public function getExeptionalCategories(){


		$sql = "SELECT * FROM  `exceptional_taxes_set_details` where exc_taxes_id = 1";
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


	function getCategoriesForExceptional($product_type,$product_id,$parent_product_id=''){

		if($product_id!=''){

			if($product_type=='conf-simple'){
				$sql = "SELECT GROUP_CONCAT(category_ids) as category_ids FROM products_category where product_id = $parent_product_id";
			}else{
				$sql = "SELECT GROUP_CONCAT(category_ids) as category_ids FROM products_category where product_id = $product_id";
			}
			$row = $this->dbl->dbl_conn->rawQueryOne($sql);


			if ($this->dbl->dbl_conn->getLastErrno() === 0){
				if ($this->dbl->dbl_conn->count > 0){
					return $row;
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

	function updateTaxPercentQuote($quote_id,$quote_item_id,$tax_percent,$tax_amount){



		$params=array($tax_percent,$tax_amount,$quote_id,$quote_item_id);
		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET tax_percent = ?, tax_amount = ? WHERE quote_id= ? AND item_id = ? ",$params);

		if($this->dbl->dbl_conn->getLastErrno() === 0){

			if ($this->dbl->dbl_conn->count > 0){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}


	}

	function updateQuoteCurrency($QuoteId,$currency_name,$currency_code_session,$currency_conversion_rate,$default_currency_flag,$currency_symbol){
        if($currency_name === ''){
            // website not using currency
            return true;
        }

		$params=array($currency_name,$currency_code_session,$currency_conversion_rate,$currency_symbol,$default_currency_flag,$QuoteId);
        $update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET currency_name = ?, currency_code_session = ?, currency_conversion_rate = ?, currency_symbol = ?, default_currency_flag = ? WHERE quote_id= ?",$params);

        if($this->dbl->dbl_conn->getLastErrno() === 0){
            return $this->dbl->dbl_conn->count > 0;
		}

        return false;
    }

	public function getTotalDefaultQtyofBundle($shopcode,$QuoteId,$product_bundle_ids,$product_id,$parent_product_id){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$parent_product_id = ($parent_product_id !='')?$parent_product_id:0;

		$sql = "SELECT SUM(products_bundles.default_qty*sales_quote_items.qty_ordered) as total_qty FROM $shop_db.products_bundles Join $shop_db.sales_quote_items ON sales_quote_items.product_id = products_bundles.bundle_product_id where IF ((products_bundles.product_type = 'configurable'), products_bundles.product_id = ".$parent_product_id .",products_bundles.product_id = ".$product_id." AND products_bundles.product_parent_id = ".$parent_product_id .") AND products_bundles.bundle_product_id IN (".$product_bundle_ids.") AND sales_quote_items.quote_id =".$QuoteId;
		$row  = $this->dbl->dbl_conn->rawQueryOne($sql);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $row['total_qty'];
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

}
