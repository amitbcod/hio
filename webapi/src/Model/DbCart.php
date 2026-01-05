<?php



use App\Application\Actions\Cart\GetCartListing;



class DbCart

{



	private $dbl;



	public function __construct()



	{



		require_once 'Config/DbLibrary.php';



		$this->dbl = new DbLibrary();

	}



	public function add_to_sales_quote($session_id, $customer_id = '')



	{







		if ($customer_id != '') {







			$params = array($session_id, $customer_id, time(), 'login', '0');



			$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO sales_quote (session_id, customer_id, created_at, checkout_method, customer_is_guest) VALUES (?, ?, ?, ?, ?)", $params);

		} else {



			$params = array($session_id, time(), 'guest', '0');



			$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO sales_quote (session_id, created_at, checkout_method, customer_is_guest) VALUES (?, ?, ?, ?)", $params);

		}







		if ($this->dbl->dbl_conn->getLastErrno() === 0) {



			$last_insert_id = $this->dbl->dbl_conn->getInsertId();



			if ($this->dbl->dbl_conn->count > 0) {



				return $last_insert_id;

			} else {







				return false;

			}

		} else {







			// echo 'Insert in UST failed. Error: '. $this->dbl->dbl_conn->getLastError();







			return false;

		}

	}















	public function add_to_sales_quote_item($quote_id, $product_type, $product_inv_type, $product_id, $product_name, $product_code, $quantity, $sku, $barcode, $price, $total_price, $created_by = '', $parent_product_id = '', $product_variants = '', $tax_percent = '', $tax_amount = '', $bundle_child_details = '',  $is_fragile_flag = 0)

	{
		$created_by_type = 0;

		if ($parent_product_id != '') {

			if (empty($barcode)) {

				$barcode = ' ';

			}

			if (empty($product_code)) {

				$product_code = ' ';

			}

			if (empty($bundle_child_details)) {

				$bundle_child_details = ' ';

			}


			if ($created_by == '') {

				$params = array($quote_id, $product_type, $product_inv_type, $product_id, $product_name, $product_code, $quantity, $sku, $barcode, $price, $total_price, $parent_product_id, $is_fragile_flag, $product_variants, $tax_percent, $tax_amount, time(), $created_by_type, $bundle_child_details, $_SERVER['REMOTE_ADDR']);

				$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO sales_quote_items (quote_id, product_type, product_inv_type, product_id, product_name, product_code, qty_ordered, sku, barcode, price, total_price, parent_product_id, is_fragile_flag, product_variants, tax_percent, tax_amount, created_at, created_by_type, bundle_child_details, ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $params);

			} else {

				$params = array($quote_id, $product_type, $product_inv_type, $product_id, $product_name, $product_code, $quantity, $sku, $barcode, $price, $total_price, $parent_product_id, $is_fragile_flag, $product_variants, $tax_percent, $tax_amount, time(), $created_by, $created_by_type, $bundle_child_details, $_SERVER['REMOTE_ADDR']);

				$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO sales_quote_items (quote_id, product_type, product_inv_type, product_id, product_name, product_code, qty_ordered, sku, barcode, price, total_price, parent_product_id, is_fragile_flag, product_variants, tax_percent, tax_amount, created_at, created_by, created_by_type, bundle_child_details, ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $params);

			}

		} else {

			if (empty($barcode)) {

				$barcode = ' ';

			}


			if (empty($product_code)) {

				$product_code = ' ';

			}

			if (empty($bundle_child_details)) {

				$bundle_child_details = ' ';

			}

			$time = time();

			if ($created_by == '') {

				$params = array($quote_id, $product_type, $product_inv_type, $product_id, $product_name, $product_code, $quantity, $sku, $barcode, $is_fragile_flag, $price, $total_price, $tax_percent, $tax_amount, $time, $created_by_type, $bundle_child_details, $_SERVER['REMOTE_ADDR']);

				$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO sales_quote_items (quote_id, product_type, product_inv_type, product_id, product_name, product_code, qty_ordered, sku, barcode, is_fragile_flag, price, total_price, tax_percent, tax_amount ,created_at, created_by_type, bundle_child_details, ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $params);

			} else {



				$params = array($quote_id, $product_type, $product_inv_type, $product_id, $product_name, $product_code, $quantity, $sku, $barcode, $is_fragile_flag, $price, $total_price, $tax_percent, $tax_amount, $time, $created_by, $created_by_type, $bundle_child_details, $_SERVER['REMOTE_ADDR']);

				$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO sales_quote_items (quote_id, product_type, product_inv_type, product_id, product_name, product_code, qty_ordered, sku, barcode, is_fragile_flag, price, total_price, tax_percent, tax_amount ,created_at, created_by, created_by_type, bundle_child_details, ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $params);

			}

		}


		if ($this->dbl->dbl_conn->getLastErrno() === 0) {







			$last_insert_id = $this->dbl->dbl_conn->getInsertId();







			if ($this->dbl->dbl_conn->count > 0) {







				return $last_insert_id;

			} else {







				return false;

			}

		} else {







			// echo 'Insert in UST failed. Error: '. $this->dbl->dbl_conn->getLastError();







			return false;

		}

	}















	public function get_product_variant_details($parent_product_id, $product_id)







	{















		$params = array($parent_product_id, $product_id);







		$result = $this->dbl->dbl_conn->rawQuery("SELECT * FROM products_variants where parent_id = ? AND product_id = ?  ORDER BY id ASC", $params);







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























	public function getAttributeDetails($attr_id)







	{







		$sql =  "SELECT * FROM eav_attributes where `id` = '$attr_id' ";







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































	public function getAttributeOptionDetails($option_id)







	{







		$sql =  "SELECT * FROM eav_attributes_options where `id` = '$option_id'";







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















	public function calculateQuoteTaxAmout($quote_id, $AllQuoteItems = false)







	{







		$total_tax_amount = 0;















		if ($AllQuoteItems != false) {















			if (is_array($AllQuoteItems) && count($AllQuoteItems) > 0) {







				$QuoteData = $this->getQuoteDataById($quote_id);























				$coupon_code = $QuoteData['coupon_code'];















				foreach ($AllQuoteItems as $value) {























					$flag = 0;















					// $ExeptionalTaxesData = $this->getExeptionalTaxesData();







					// $ExeptionalCategories = $this->getExeptionalCategories();















					$ExeptionalTaxesData = '';







					$ExeptionalCategories = '';























					// if(!empty($ExeptionalTaxesData) && count($ExeptionalTaxesData) > 0 ){















					// 	if($value['product_type']=='conf-simple'){















					// 		echo "666";







					// 		exit;







					// 		$ProductCategory=$this->getCategoriesForExceptional($value['product_type'],$value['product_id'],$value['parent_product_id']);







					// 	}else{















					// 		echo "777";







					// 		exit;







					// 		$ProductCategory=$this->getCategoriesForExceptional($value['product_type'],$value['product_id']);







					// 	}















					// 	$Productscat = explode(',',$ProductCategory['category_ids']);















					// 	if(!empty($ExeptionalCategories) && count($ExeptionalCategories) > 0 ){







					// 		foreach($ExeptionalCategories as $ExpcatId){







					// 			if(in_array($ExpcatId['category_id'], $Productscat)) {







					// 				$flag = 1;







					// 				break;







					// 			}else {







					// 			}







					// 		}







					// 	}















					// }























					if ($coupon_code != "" && $value['price'] > 0.00 && $value['discount_percent'] > 0.00) {







						$pro_price_incl_tax = $value['price'] - ($value['price'] * $value['discount_percent']) / 100;

					} else {







						$pro_price_incl_tax = $value['price'];

					}















					$tax_amount = 0;







					$tax_amount_item = 0;























					if (!empty($ExeptionalTaxesData) && ($pro_price_incl_tax < $ExeptionalTaxesData['less_than_amount']) && $flag == 1) {







						$tax_percent = $ExeptionalTaxesData['less_than_tax_percent'];















						if ($tax_percent > 0.00 && $pro_price_incl_tax > 0.00) {







							$pro_price_excl_tax = $pro_price_incl_tax / ((100 + $tax_percent) / 100);







							$tax_amount_item =  $pro_price_incl_tax - $pro_price_excl_tax;

						}















						$this->updateTaxPercentQuote($value['quote_id'], $value['item_id'], $tax_percent, $tax_amount_item);

					} else {















						$tax_percent = $value['tax_percent'];















						if ($tax_percent > 0.00 && $pro_price_incl_tax > 0.00) {







							$pro_price_excl_tax = $pro_price_incl_tax / ((100 + $tax_percent) / 100);







							$tax_amount_item =  $pro_price_incl_tax - $pro_price_excl_tax;

						}







						$this->updateTaxPercentQuote($value['quote_id'], $value['item_id'], $tax_percent, $tax_amount_item);

					}































					if ($tax_percent > 0.00 && $pro_price_incl_tax > 0.00) {







						$pro_price_excl_tax = $pro_price_incl_tax / ((100 + $tax_percent) / 100);







						$tax_amount =  $pro_price_incl_tax - $pro_price_excl_tax;







						$total_tax_amount = $total_tax_amount + ($tax_amount * $value['qty_ordered']);

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































	public function getTotalQuantityByQuote($quote_id)







	{







		$sql = "SELECT sum(qty_ordered) as total_qty FROM sales_quote_items where quote_id = '$quote_id'";







		$row = $this->dbl->dbl_conn->rawQueryOne($sql);







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















	public function getQuoteDataById($quote_id)







	{







		$sql =  "SELECT * FROM sales_quote where `quote_id` = '$quote_id'";







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















	public function getCouponDataByCode($coupon_code)







	{







		$sql = "SELECT * FROM salesrule_coupon WHERE coupon_code = '$coupon_code'";







		$row = $this->dbl->dbl_conn->rawQueryOne($sql);







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















	public function getQuoteItemDataById($item_id)







	{







		$sql = "SELECT * FROM sales_quote_items where `item_id` = '$item_id'";















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



	public function updateCouponDiscount($quote_id, $couponData = array(), $cartData = array(), $flag = '')

	{



		$coupon_code = $couponData['coupon_code'];



		if ((isset($couponData) && count($couponData) > 0) &&  (isset($cartData['cartItems']) && count($cartData['cartItems']) > 0)) {



			// echo "<pre>";

			// print_r($couponData);exit();



			$cartItems = array();



			$rule_ids = '';



			$status_flag = false;



			$TotalInfo = $this->getTotalByQuote($quote_id);



			$base_subtotal = $TotalInfo['total'];



			$QuoteData = $this->getQuoteDataById($quote_id);



			$subtotal = $QuoteData['subtotal'];



			$customer_id = $QuoteData['customer_id'];



			$cartItems = (isset($cartData['cartItems']) && count($cartData['cartItems']) > 0) ? $cartData['cartItems'] : array();



			$rule_ids = ',' . $couponData['rule_id'];



			if ($couponData['coupon_type'] == 1) {



				/*---------------------Voucher Start------------------------------------*/



				if ($couponData['min_cart_value'] != '' && ($base_subtotal < $couponData['min_cart_value'])) {







					if ($flag == 'apply_btn') {







						$status_flag = false;

					} else {







						$this->removeCouponCode($quote_id, $couponData['coupon_code'], $couponData['coupon_type']);







						$status_flag = true;

					}

				} else {



					//added by al



					$usge_per_coupon = $couponData['usge_per_coupon'];



					$usage_per_customer = $couponData['usage_per_customer'];



					if ($couponData['type'] == 4) {















						if ($customer_id > 0 && ($usage_per_customer != 0 && $usage_per_customer > 0)) {







							$CouponUsedInOrderByCustomer = $this->getCouponUsedCountInOrders($couponData['coupon_code'], $couponData['coupon_type'], $customer_id);























							if ($CouponUsedInOrderByCustomer != false) {















								$order_usge_per_customer = (isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer > 0) ? $CouponUsedInOrderByCustomer : 0;

							} else {







								$order_usge_per_customer = 0;

							}















							if (($order_usge_per_customer > 0) && ($order_usge_per_customer >= $usage_per_customer)) {















								$status_flag = false;

							} else {























								if ($couponData['apply_type'] == 'by_percent') {







									$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







									$discount_percent = $couponData['discount_amount'];







									$status_flag = true;

								} else if ($couponData['apply_type'] == 'by_fixed') {







									$disc_amount = $couponData['discount_amount'];







									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







									$discount_percent = 0;







									$status_flag = true;

								}

							}

						} else {







							if ($couponData['apply_type'] == 'by_percent') {







								$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







								$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







								$discount_percent = $couponData['discount_amount'];







								$status_flag = true;

							} else if ($couponData['apply_type'] == 'by_fixed') {







								$disc_amount = $couponData['discount_amount'];







								$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







								$discount_percent = 0;







								$status_flag = true;

							}

						}

					} else if ($couponData['type'] == 3 && $couponData['usge_per_coupon'] != 0 && $couponData['usge_per_coupon'] > 0) {



						$CouponUsedInOrder = $this->getCouponUsedCountInOrders($couponData['coupon_code'], $couponData['coupon_type']);



						if ($usge_per_coupon != 0 || $usge_per_coupon > 0) {



							if ($CouponUsedInOrder != false) {



								$order_usge_per_coupon = (isset($CouponUsedInOrder) && $CouponUsedInOrder > 0) ? $CouponUsedInOrder : 0;

							} else {



								$order_usge_per_coupon = 0;

							}



							if (($order_usge_per_coupon > 0) && ($order_usge_per_coupon >= $usge_per_coupon)) {



								$status_flag = false;

							} else {



								if ($customer_id > 0 && ($usage_per_customer != 0 && $usage_per_customer > 0)) {



									$CouponUsedInOrderByCustomer = $this->getCouponUsedCountInOrders($couponData['coupon_code'], $couponData['coupon_type'], $customer_id);



									if ($CouponUsedInOrderByCustomer != false) {



										$order_usge_per_customer = (isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer > 0) ? $CouponUsedInOrderByCustomer : 0;

									} else {



										$order_usge_per_customer = 0;

									}



									if (($order_usge_per_customer > 0) && ($order_usge_per_customer >= $usage_per_customer)) {



										$status_flag = false;

									} else {



										if ($couponData['apply_type'] == 'by_percent') {



											$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));



											$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



											$discount_percent = $couponData['discount_amount'];



											$status_flag = true;

										} else if ($couponData['apply_type'] == 'by_fixed') {



											$disc_amount = $couponData['discount_amount'];



											$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



											$discount_percent = 0;



											$status_flag = true;

										}

									}

								} else {



									if ($couponData['apply_type'] == 'by_percent') {



										$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));



										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



										$discount_percent = $couponData['discount_amount'];



										$status_flag = true;

									} else if ($couponData['apply_type'] == 'by_fixed') {



										$disc_amount = $couponData['discount_amount'];



										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



										$discount_percent = 0;



										$status_flag = true;

									}

								}

							}

						}

					} else  if (($couponData['type'] == 3) && ($couponData['usge_per_coupon'] == 0) && ($usage_per_customer != 0 && $usage_per_customer > 0)) {



						if ($customer_id > 0 && ($usage_per_customer != 0 && $usage_per_customer > 0)) {



							$CouponUsedInOrderByCustomer = $this->getCouponUsedCountInOrders($couponData['coupon_code'], $couponData['coupon_type'], $customer_id);



							if ($CouponUsedInOrderByCustomer != false) {



								$order_usge_per_customer = (isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer > 0) ? $CouponUsedInOrderByCustomer : 0;

							} else {



								$order_usge_per_customer = 0;

							}



							if (($order_usge_per_customer > 0) && ($order_usge_per_customer >= $usage_per_customer)) {



								$status_flag = false;

							} else {



								if ($couponData['apply_type'] == 'by_percent') {



									$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));



									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



									$discount_percent = $couponData['discount_amount'];



									$status_flag = true;

								} else if ($couponData['apply_type'] == 'by_fixed') {



									$disc_amount = $couponData['discount_amount'];



									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



									$discount_percent = 0;



									$status_flag = true;

								}

							}

						} else {







							if ($couponData['apply_type'] == 'by_percent') {







								$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







								$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







								$discount_percent = $couponData['discount_amount'];







								$status_flag = true;

							} else if ($couponData['apply_type'] == 'by_fixed') {







								$disc_amount = $couponData['discount_amount'];







								$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







								$discount_percent = 0;







								$status_flag = true;

							}

						}

					} else {



						if ($couponData['apply_type'] == 'by_percent') {



							$disc_amount = ($subtotal * ($couponData['discount_amount'] / 100));



							$discount_amount = ($disc_amount > $subtotal) ? $subtotal : $disc_amount;



							$discount_percent = $couponData['discount_amount'];



							$status_flag = true;

						} else if ($couponData['apply_type'] == 'by_fixed') {



							$disc_amount = $couponData['discount_amount'];



							$discount_amount = ($disc_amount > $subtotal) ? $subtotal : $disc_amount;



							$discount_percent = 0;



							$status_flag = true;

						}

					}

				}



				/*---------------------Voucher End------------------------------------*/

			} else if ($couponData['coupon_type'] == 0) {







				if ($couponData['type'] == 1) {







					/*----------Catalog discount by AL :start------------------------------------*/







					$discount_percent = $couponData['discount_amount'];







					$discount_amount = 0.00;















					$ApplyOnCategory = $couponData['apply_on_categories'];







					if (strpos($ApplyOnCategory, ',') !== false) {







						$CategoryArr = explode(',', $ApplyOnCategory);







						$CategoryArr = array_filter(array_unique($CategoryArr));

					} else {







						$CategoryArr[] = $ApplyOnCategory;

					}















					if (is_array($cartItems) && count($cartItems) > 0) {







						foreach ($cartItems as $value) {







							if ($value['product_type'] == 'conf-simple') {







								$ProductCategory = $this->getProductCategory($value['product_type'], $value['product_id'], $value['parent_product_id']);

							} else {







								$ProductCategory = $this->getProductCategory($value['product_type'], $value['product_id']);

							}























							if ($ProductCategory != false) {















								if (strpos($ApplyOnCategory, ',') !== false) {







									$CategoryArr = explode(',', $ApplyOnCategory);







									$CategoryArr = array_filter(array_unique($CategoryArr));

								} else {







									$CategoryArr[] = $ApplyOnCategory;

								}







								$ProductCategoryArr = explode(',', $ProductCategory['category_ids']);















								$IsCategoryExist = !empty(array_intersect($ProductCategoryArr, $CategoryArr));















								if ($IsCategoryExist == 1) {















									$item_id = $value['item_id'];







									$qty = $value['qty_ordered'];















									$perItemDiscount = $value['price'] * ($discount_percent / 100);







									$total_discount_amount = $perItemDiscount * $qty;















									$discount_amount = $discount_amount + $total_discount_amount;















									$params = array($rule_ids, $discount_percent, $perItemDiscount, $total_discount_amount, time(), $item_id, $quote_id);







									$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ?,updated_at=? WHERE item_id = ? AND quote_id= ?", $params);















									if ($this->dbl->dbl_conn->getLastErrno() === 0) {







										$status_flag = true;

									}

								}

							}

						}







						//return $apply_flag;







					} else {







						//return $apply_flag;







					}















					/*------------------------------------End------------------------------------*/

				} else if ($couponData['type'] == 2) {





					if ($couponData['min_cart_value'] != '' && ($base_subtotal < $couponData['min_cart_value'])) {







						if ($flag == 'apply_btn') {







							$status_flag = false;

						} else {







							$this->removeCouponCode($quote_id, $couponData['coupon_code'], $couponData['coupon_type']);







							$status_flag = true;

						}

					} else {









						/*----------Product discount by AL :start------------------------------------*/















						$discount_percent = $couponData['discount_amount'];







						$discount_amount = 0.00;















						$ApplyOnProduct = $couponData['apply_on_products'];







						if (strpos($ApplyOnProduct, ',') !== false) {







							$ProductArr = explode(',', $ApplyOnProduct);







							$ProductArr = array_filter(array_unique($ProductArr));

						} else {







							$ProductArr[] = $ApplyOnProduct;

						}







						if (is_array($cartItems) && count($cartItems) > 0) {







							foreach ($cartItems as $value) {



								if (is_array($ProductArr) && in_array($value['product_id'], $ProductArr)) {



									$item_id = $value['item_id'];



									$qty = $value['qty_ordered'];



									$perItemDiscount = $value['price'] * ($discount_percent / 100);



									$total_discount_amount = $perItemDiscount * $qty;



									$discount_amount = $discount_amount + $total_discount_amount;





									// $params = array($rule_ids, $couponData['discount_amount'], $perItemDiscount, $total_discount_amount, time(), $item_id, $quote_id);

									// $update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ?,updated_at=? WHERE item_id = ? AND quote_id= ?", $params);





									$params = array($rule_ids, $couponData['discount_amount'], $perItemDiscount, $total_discount_amount, time(), $item_id, $quote_id);







									$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ?,updated_at=? WHERE item_id = ? AND quote_id= ?", $params);





									if ($this->dbl->dbl_conn->getLastErrno() === 0) {



										$status_flag = true;

									} else {



										$status_flag = false;

									}

								}

							}

						} else {







							$status_flag = false;

						}

					}

					/*------------------------------------End------------------------------------*/

				} else if ($couponData['type'] == 3 || $couponData['type'] == 4) {



					$usge_per_coupon = $couponData['usge_per_coupon'];



					$usage_per_customer = $couponData['usage_per_customer'];



					if ($couponData['apply_condition'] == 'discount_on_mincartval') {



						if ($couponData['min_cart_value'] != '' && ($base_subtotal < $couponData['min_cart_value'])) {



							if ($flag == 'apply_btn') {







								$status_flag = false;

							} else {







								$this->removeCouponCode($quote_id, $couponData['coupon_code'], $couponData['coupon_type']);







								$status_flag = true;

							}

						} else if ($couponData['type'] == 4) {















							if ($customer_id > 0 && ($usage_per_customer != 0 && $usage_per_customer > 0)) {







								$CouponUsedInOrderByCustomer = $this->getCouponUsedCountInOrders($couponData['coupon_code'], $couponData['coupon_type'], $customer_id);























								if ($CouponUsedInOrderByCustomer != false) {















									$order_usge_per_customer = (isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer > 0) ? $CouponUsedInOrderByCustomer : 0;

								} else {







									$order_usge_per_customer = 0;

								}















								if (($order_usge_per_customer > 0) && ($order_usge_per_customer >= $usage_per_customer)) {















									$status_flag = false;

								} else {























									if ($couponData['apply_type'] == 'by_percent') {







										$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







										$discount_percent = $couponData['discount_amount'];







										$update_cart_item = $this->updateCartItemsCouponDiscount($quote_id, $rule_ids, $discount_percent, $cartItems);







										$status_flag = true;

									} else if ($couponData['apply_type'] == 'by_fixed') {







										$disc_amount = $couponData['discount_amount'];







										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







										$discount_percent = 0;







										$status_flag = true;

									}

								}

							} else {















								if ($couponData['apply_type'] == 'by_percent') {







									$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







									$discount_percent = $couponData['discount_amount'];















									$update_cart_item = $this->updateCartItemsCouponDiscount($quote_id, $rule_ids, $discount_percent, $cartItems);







									$status_flag = true;

								} else if ($couponData['apply_type'] == 'by_fixed') {







									$disc_amount = $couponData['discount_amount'];







									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







									$discount_percent = 0;







									$status_flag = true;

								}

							}

						} else if ($couponData['type'] == 3 && $couponData['usge_per_coupon'] != 0 && $couponData['usge_per_coupon'] > 0) {







							$CouponUsedInOrder = $this->getCouponUsedCountInOrders($couponData['coupon_code'], $couponData['coupon_type']);







							if ($usge_per_coupon != 0 || $usge_per_coupon > 0) {















								if ($CouponUsedInOrder != false) {







									$order_usge_per_coupon = (isset($CouponUsedInOrder) && $CouponUsedInOrder > 0) ? $CouponUsedInOrder : 0;

								} else {







									$order_usge_per_coupon = 0;

								}















								if (($order_usge_per_coupon > 0) && ($order_usge_per_coupon >= $usge_per_coupon)) {















									$status_flag = false;

								} else {















									if ($customer_id > 0 && ($usage_per_customer != 0 && $usage_per_customer > 0)) {







										$CouponUsedInOrderByCustomer = $this->getCouponUsedCountInOrders($couponData['coupon_code'], $couponData['coupon_type'], $customer_id);















										if ($CouponUsedInOrderByCustomer != false) {







											$order_usge_per_customer = (isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer > 0) ? $CouponUsedInOrderByCustomer : 0;

										} else {







											$order_usge_per_customer = 0;

										}















										if (($order_usge_per_customer > 0) && ($order_usge_per_customer >= $usage_per_customer)) {







											$status_flag = false;

										} else {















											if ($couponData['apply_type'] == 'by_percent') {







												$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







												$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







												$discount_percent = $couponData['discount_amount'];















												$update_cart_item = $this->updateCartItemsCouponDiscount($quote_id, $rule_ids, $discount_percent, $cartItems);







												$status_flag = true;

											} else if ($couponData['apply_type'] == 'by_fixed') {







												$disc_amount = $couponData['discount_amount'];







												$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







												$discount_percent = 0;







												$status_flag = true;

											}

										}

									} else {







										if ($couponData['apply_type'] == 'by_percent') {







											$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







											$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







											$discount_percent = $couponData['discount_amount'];















											$update_cart_item = $this->updateCartItemsCouponDiscount($quote_id, $rule_ids, $discount_percent, $cartItems);







											$status_flag = true;

										} else if ($couponData['apply_type'] == 'by_fixed') {







											$disc_amount = $couponData['discount_amount'];







											$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







											$discount_percent = 0;







											$status_flag = true;

										}

									}

								}

							}

						} else  if (($couponData['type'] == 3) && ($couponData['usge_per_coupon'] == 0) && ($usage_per_customer != 0 && $usage_per_customer > 0)) {















							if ($customer_id > 0 && ($usage_per_customer != 0 && $usage_per_customer > 0)) {







								$CouponUsedInOrderByCustomer = $this->getCouponUsedCountInOrders($couponData['coupon_code'], $couponData['coupon_type'], $customer_id);















								if ($CouponUsedInOrderByCustomer != false) {







									$order_usge_per_customer = (isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer > 0) ? $CouponUsedInOrderByCustomer : 0;

								} else {







									$order_usge_per_customer = 0;

								}















								if (($order_usge_per_customer > 0) && ($order_usge_per_customer >= $usage_per_customer)) {







									$status_flag = false;

								} else {







									if ($couponData['apply_type'] == 'by_percent') {







										$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







										$discount_percent = $couponData['discount_amount'];















										$update_cart_item = $this->updateCartItemsCouponDiscount($quote_id, $rule_ids, $discount_percent, $cartItems);







										$status_flag = true;

									} else if ($couponData['apply_type'] == 'by_fixed') {







										$disc_amount = $couponData['discount_amount'];







										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







										$discount_percent = 0;







										$status_flag = true;

									}

								}

							} else {







								if ($couponData['apply_type'] == 'by_percent') {







									$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







									$discount_percent = $couponData['discount_amount'];















									$update_cart_item = $this->updateCartItemsCouponDiscount($quote_id, $rule_ids, $discount_percent, $cartItems);







									$status_flag = true;

								} else if ($couponData['apply_type'] == 'by_fixed') {







									$disc_amount = $couponData['discount_amount'];







									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







									$discount_percent = 0;







									$status_flag = true;

								}

							}

						} else {















							/*--------final else--------------------------------------------*/







							if ($couponData['apply_type'] == 'by_percent') {







								$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







								$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







								$discount_percent = $couponData['discount_amount'];















								$update_cart_item = $this->updateCartItemsCouponDiscount($quote_id, $rule_ids, $discount_percent, $cartItems);







								$status_flag = true;

							} else if ($couponData['apply_type'] == 'by_fixed') {







								$disc_amount = $couponData['discount_amount'];







								$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







								$discount_percent = 0;







								$status_flag = true;

							}







							/*-----------------Final else----------------------*/

						}

					}







					/*---------------------------------------------*/

				}

			}







			if ($status_flag == true) {







				$update_quote = $this->UpateQuoteCouponDiscount($quote_id, $rule_ids, $couponData['coupon_code'], $discount_amount, $discount_percent, $couponData['coupon_type']);







				return true;

			} else {







				return false;

			}

		} else {







			return false;

		}

	}



	public function updateCouponDiscountNew($quote_id, $couponData = array(), $cartData = array(), $flag = '')

	{







		$coupon_code = $couponData['coupon_code'];







		if ((isset($couponData) && count($couponData) > 0) &&  (isset($cartData['cartItems']) && count($cartData['cartItems']) > 0)) {



			// echo "<pre>";

			// print_r($couponData);exit();



			$cartItems = array();







			$rule_ids = '';















			$status_flag = false;















			$TotalInfo = $this->getTotalByQuote($quote_id);







			$base_subtotal = $TotalInfo['total'];















			$QuoteData = $this->getQuoteDataById($quote_id);







			$subtotal = $QuoteData['subtotal'];







			$customer_id = $QuoteData['customer_id'];















			$cartItems = (isset($cartData['cartItems']) && count($cartData['cartItems']) > 0) ? $cartData['cartItems'] : array();







			$rule_ids = ',' . $couponData['rule_id'];















			if ($couponData['coupon_type'] == 1) {







				/*---------------------Voucher Start------------------------------------*/







				if ($couponData['min_cart_value'] != '' && ($base_subtotal < $couponData['min_cart_value'])) {







					if ($flag == 'apply_btn') {







						$status_flag = false;

					} else {







						$this->removeCouponCode($quote_id, $couponData['coupon_code'], $couponData['coupon_type']);







						$status_flag = true;

					}

				} else {







					//added by al







					$usge_per_coupon = $couponData['usge_per_coupon'];







					$usage_per_customer = $couponData['usage_per_customer'];















					if ($couponData['type'] == 4) {















						if ($customer_id > 0 && ($usage_per_customer != 0 && $usage_per_customer > 0)) {







							$CouponUsedInOrderByCustomer = $this->getCouponUsedCountInOrders($couponData['coupon_code'], $couponData['coupon_type'], $customer_id);























							if ($CouponUsedInOrderByCustomer != false) {















								$order_usge_per_customer = (isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer > 0) ? $CouponUsedInOrderByCustomer : 0;

							} else {







								$order_usge_per_customer = 0;

							}















							if (($order_usge_per_customer > 0) && ($order_usge_per_customer >= $usage_per_customer)) {















								$status_flag = false;

							} else {























								if ($couponData['apply_type'] == 'by_percent') {







									$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







									$discount_percent = $couponData['discount_amount'];







									$status_flag = true;

								} else if ($couponData['apply_type'] == 'by_fixed') {







									$disc_amount = $couponData['discount_amount'];







									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







									$discount_percent = 0;







									$status_flag = true;

								}

							}

						} else {







							if ($couponData['apply_type'] == 'by_percent') {







								$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







								$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







								$discount_percent = $couponData['discount_amount'];







								$status_flag = true;

							} else if ($couponData['apply_type'] == 'by_fixed') {







								$disc_amount = $couponData['discount_amount'];







								$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







								$discount_percent = 0;







								$status_flag = true;

							}

						}

					} else if ($couponData['type'] == 3 && $couponData['usge_per_coupon'] != 0 && $couponData['usge_per_coupon'] > 0) {















						$CouponUsedInOrder = $this->getCouponUsedCountInOrders($couponData['coupon_code'], $couponData['coupon_type']);























						if ($usge_per_coupon != 0 || $usge_per_coupon > 0) {















							if ($CouponUsedInOrder != false) {







								$order_usge_per_coupon = (isset($CouponUsedInOrder) && $CouponUsedInOrder > 0) ? $CouponUsedInOrder : 0;

							} else {







								$order_usge_per_coupon = 0;

							}















							if (($order_usge_per_coupon > 0) && ($order_usge_per_coupon >= $usge_per_coupon)) {















								$status_flag = false;

							} else {















								if ($customer_id > 0 && ($usage_per_customer != 0 && $usage_per_customer > 0)) {







									$CouponUsedInOrderByCustomer = $this->getCouponUsedCountInOrders($couponData['coupon_code'], $couponData['coupon_type'], $customer_id);







									if ($CouponUsedInOrderByCustomer != false) {







										$order_usge_per_customer = (isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer > 0) ? $CouponUsedInOrderByCustomer : 0;

									} else {







										$order_usge_per_customer = 0;

									}















									if (($order_usge_per_customer > 0) && ($order_usge_per_customer >= $usage_per_customer)) {







										$status_flag = false;

									} else {















										if ($couponData['apply_type'] == 'by_percent') {







											$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







											$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







											$discount_percent = $couponData['discount_amount'];







											$status_flag = true;

										} else if ($couponData['apply_type'] == 'by_fixed') {







											$disc_amount = $couponData['discount_amount'];







											$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







											$discount_percent = 0;







											$status_flag = true;

										}

									}

								} else {















									if ($couponData['apply_type'] == 'by_percent') {







										$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







										$discount_percent = $couponData['discount_amount'];







										$status_flag = true;

									} else if ($couponData['apply_type'] == 'by_fixed') {







										$disc_amount = $couponData['discount_amount'];







										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







										$discount_percent = 0;







										$status_flag = true;

									}

								}

							}

						}

					} else  if (($couponData['type'] == 3) && ($couponData['usge_per_coupon'] == 0) && ($usage_per_customer != 0 && $usage_per_customer > 0)) {







						if ($customer_id > 0 && ($usage_per_customer != 0 && $usage_per_customer > 0)) {







							$CouponUsedInOrderByCustomer = $this->getCouponUsedCountInOrders($couponData['coupon_code'], $couponData['coupon_type'], $customer_id);















							if ($CouponUsedInOrderByCustomer != false) {







								$order_usge_per_customer = (isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer > 0) ? $CouponUsedInOrderByCustomer : 0;

							} else {







								$order_usge_per_customer = 0;

							}















							if (($order_usge_per_customer > 0) && ($order_usge_per_customer >= $usage_per_customer)) {







								$status_flag = false;

							} else {















								if ($couponData['apply_type'] == 'by_percent') {







									$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







									$discount_percent = $couponData['discount_amount'];







									$status_flag = true;

								} else if ($couponData['apply_type'] == 'by_fixed') {







									$disc_amount = $couponData['discount_amount'];







									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







									$discount_percent = 0;







									$status_flag = true;

								}

							}

						} else {







							if ($couponData['apply_type'] == 'by_percent') {







								$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







								$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







								$discount_percent = $couponData['discount_amount'];







								$status_flag = true;

							} else if ($couponData['apply_type'] == 'by_fixed') {







								$disc_amount = $couponData['discount_amount'];







								$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







								$discount_percent = 0;







								$status_flag = true;

							}

						}

					} else {







						if ($couponData['apply_type'] == 'by_percent') {







							$disc_amount = ($subtotal * ($couponData['discount_amount'] / 100));







							$discount_amount = ($disc_amount > $subtotal) ? $subtotal : $disc_amount;







							$discount_percent = $couponData['discount_amount'];







							$status_flag = true;

						} else if ($couponData['apply_type'] == 'by_fixed') {







							$disc_amount = $couponData['discount_amount'];







							$discount_amount = ($disc_amount > $subtotal) ? $subtotal : $disc_amount;







							$discount_percent = 0;







							$status_flag = true;

						}

					}

				}







				/*---------------------Voucher End------------------------------------*/

			} else if ($couponData['coupon_type'] == 0) {







				if ($couponData['type'] == 1) {







					/*----------Catalog discount by AL :start------------------------------------*/







					$discount_percent = $couponData['discount_amount'];







					$discount_amount = 0.00;















					$ApplyOnCategory = $couponData['apply_on_categories'];







					if (strpos($ApplyOnCategory, ',') !== false) {







						$CategoryArr = explode(',', $ApplyOnCategory);







						$CategoryArr = array_filter(array_unique($CategoryArr));

					} else {







						$CategoryArr[] = $ApplyOnCategory;

					}















					if (is_array($cartItems) && count($cartItems) > 0) {







						foreach ($cartItems as $value) {







							if ($value['product_type'] == 'conf-simple') {







								$ProductCategory = $this->getProductCategory($value['product_type'], $value['product_id'], $value['parent_product_id']);

							} else {







								$ProductCategory = $this->getProductCategory($value['product_type'], $value['product_id']);

							}























							if ($ProductCategory != false) {















								if (strpos($ApplyOnCategory, ',') !== false) {







									$CategoryArr = explode(',', $ApplyOnCategory);







									$CategoryArr = array_filter(array_unique($CategoryArr));

								} else {







									$CategoryArr[] = $ApplyOnCategory;

								}



								$ProductCategoryArr = explode(',', $ProductCategory['category_ids']);



								$IsCategoryExist = !empty(array_intersect($ProductCategoryArr, $CategoryArr));



								if ($IsCategoryExist == 1) {



									$item_id = $value['item_id'];



									$qty = $value['qty_ordered'];

									// print_r($value['product_id']);die;

									$getProduct = $this->getProduct($value['product_id']);

									// echo "<pre>";

									// print_r($getProduct);

									// die;



									// if ($getProduct !== false) {

									// 	$shipping_amount = $getProduct['shipping_amount'];



									// 	$Total_after_shipping_deduction = $value['price'] - $shipping_amount;

									// } else {

									// 	$shipping_amount = 0;

									// 	$Total_after_shipping_deduction = $value['price']; // No deduction if shipping amount not found

									// }



									$perItemDiscount = $value['price'] * ($couponData['discount_amount'] / 100);

									// $total_after_discount_deduction = $Total_after_shipping_deduction - $perItemDiscount;

									// $Adding_back_shipping_charges = $total_after_discount_deduction + $shipping_amount;



									$total_discount_amount = $perItemDiscount * $qty;



									$discount_amount = $discount_amount + $total_discount_amount;



									if ($couponData['rule_id'] == '146') {

										$variants = json_decode($value['product_variants'], true);

										// print_r($variants['0']['Subscription']);



										// if ($variants['0']['Subscription'] == '1 Year') {

										// 	// echo "h1";

										// 	$params = array($rule_ids, $discount_percent, $perItemDiscount, $total_discount_amount, time(), $item_id, $quote_id);



										// 	$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ?,updated_at=? WHERE item_id = ? AND quote_id= ?", $params);

										// } else {

										// 	// echo "h2";

										// }



										if ($value['product_id'] == '3958' || $value['product_id'] == '3959' ||  $value['product_id'] == '3960' ||  $value['product_id'] == '3961' ||  $value['product_id'] == '3962' ||  $value['product_id'] == '3963') {

											$params = array($rule_ids, $couponData['discount_amount'], $perItemDiscount, $total_discount_amount, time(), $item_id, $quote_id);

											$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ?,updated_at=? WHERE item_id = ? AND quote_id= ?", $params);

											// if ($variants['0']['Subscription'] == '1 Year Print + 1 Year Digital' || $variants['0']['Subscription'] == '2 Year Print + 2 Year Digital' || $variants['0']['Subscription'] == '3 Year Print + 3 Year Digital') {

											// }



											// $params1 = array($Adding_back_shipping_charges, $Adding_back_shipping_charges, time(), $quote_id);

											// $update_row1 = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET grand_total=?,base_grand_total=?, updated_at = ?  WHERE quote_id=?", $params1);

										}

										if ($variants['0']['Subscription'] == '1 Year' || $variants[0]['Subscription'] == '1 Years' || $variants[0]['Subscription'] == '1 Year Print') {

											// echo "h1";

											$params = array($rule_ids, $couponData['discount_amount'], $perItemDiscount, $total_discount_amount, time(), $item_id, $quote_id);

											$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ?,updated_at=? WHERE item_id = ? AND quote_id= ?", $params);



											// $params1 = array($Adding_back_shipping_charges, $Adding_back_shipping_charges, time(), $quote_id);

											// $update_row1 = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET grand_total=?,base_grand_total=?, updated_at = ?  WHERE quote_id=?", $params1);



											// $params1 = array($Adding_back_shipping_charges, time(), $quote_id);

											// $update_row1 = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET grand_total=?, updated_at = ?  WHERE quote_id=?", $params);

										}

									}

									if ($couponData['rule_id'] == '149') {

										$variants = json_decode($value['product_variants'], true);

										// print_r($variants['0']['Subscription']);



										// if ($variants['0']['Subscription'] == '1 Year') {

										// 	// echo "h1";

										// 	$params = array($rule_ids, $discount_percent, $perItemDiscount, $total_discount_amount, time(), $item_id, $quote_id);



										// 	$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ?,updated_at=? WHERE item_id = ? AND quote_id= ?", $params);

										// } else {

										// 	// echo "h2";

										// }



										if ($value['product_id'] == '3958' || $value['product_id'] == '3959' ||  $value['product_id'] == '3960' ||  $value['product_id'] == '3961' ||  $value['product_id'] == '3962' ||  $value['product_id'] == '3963') {

											$params = array($rule_ids, $couponData['discount_amount'], $perItemDiscount, $total_discount_amount, time(), $item_id, $quote_id);

											$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ?,updated_at=? WHERE item_id = ? AND quote_id= ?", $params);

											// if ($variants['0']['Subscription'] == '1 Year Print + 1 Year Digital' || $variants['0']['Subscription'] == '2 Year Print + 2 Year Digital' || $variants['0']['Subscription'] == '3 Year Print + 3 Year Digital') {

											// }



											// $params1 = array($Adding_back_shipping_charges, $Adding_back_shipping_charges, time(), $quote_id);

											// $update_row1 = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET grand_total=?,base_grand_total=?, updated_at = ?  WHERE quote_id=?", $params1);

										}

										if ($variants['0']['Subscription'] == '1 Years' || $variants['0']['Subscription'] == '1 Year' || $variants['0']['Subscription'] == '6 Months') {

											// echo "h1";

											$params = array($rule_ids, $couponData['discount_amount'], $perItemDiscount, $total_discount_amount, time(), $item_id, $quote_id);

											$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ?,updated_at=? WHERE item_id = ? AND quote_id= ?", $params);



											// $params1 = array($Adding_back_shipping_charges, $Adding_back_shipping_charges, time(), $quote_id);

											// $update_row1 = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET grand_total=?,base_grand_total=?, updated_at = ?  WHERE quote_id=?", $params1);



											// $params1 = array($Adding_back_shipping_charges, time(), $quote_id);

											// $update_row1 = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET grand_total=?, updated_at = ?  WHERE quote_id=?", $params);

										}

									}





									if ($this->dbl->dbl_conn->getLastErrno() === 0) {







										$status_flag = true;

									}

								}

							}

						}







						//return $apply_flag;







					} else {







						//return $apply_flag;







					}















					/*------------------------------------End------------------------------------*/

				} else if ($couponData['type'] == 2) {





					if ($couponData['min_cart_value'] != '' && ($base_subtotal < $couponData['min_cart_value'])) {







						if ($flag == 'apply_btn') {







							$status_flag = false;

						} else {







							$this->removeCouponCode($quote_id, $couponData['coupon_code'], $couponData['coupon_type']);







							$status_flag = true;

						}

					} else {



						/*----------Product discount by AL :start------------------------------------*/



						$discount_percent = $couponData['discount_amount'];





						$discount_amount = 0.00;



						$ApplyOnProduct = $couponData['apply_on_products'];



						if (strpos($ApplyOnProduct, ',') !== false) {



							$ProductArr = explode(',', $ApplyOnProduct);



							$ProductArr = array_filter(array_unique($ProductArr));

						} else {



							$ProductArr[] = $ApplyOnProduct;

						}



						if (is_array($cartItems) && count($cartItems) > 0) {



							foreach ($cartItems as $value) {



								if (is_array($ProductArr) && in_array($value['product_id'], $ProductArr)) {



									$item_id = $value['item_id'];



									$qty = $value['qty_ordered'];



									// print_r($value['product_id']);die;

									$getProduct = $this->getProduct($value['product_id']);



									// if ($getProduct !== false) {

									// 	$shipping_amount = $getProduct['shipping_amount'];



									// 	$Total_after_shipping_deduction = $value['price'] - $shipping_amount;

									// } else {

									// 	$shipping_amount = 0;

									// 	$Total_after_shipping_deduction = $value['price']; // No deduction if shipping amount not found

									// }



									$perItemDiscount = $value['price'] * ($couponData['discount_amount'] / 100);

									// $total_after_discount_deduction = $Total_after_shipping_deduction - $perItemDiscount;

									// $Adding_back_shipping_charges = $total_after_discount_deduction + $shipping_amount;



									$total_discount_amount = $perItemDiscount * $qty;



									$discount_amount = $discount_amount + $total_discount_amount;



									if ($couponData['rule_id'] == '146') {

										$variants = json_decode($value['product_variants'], true);

										// print_r($variants['0']['Subscription']);



										if ($value['product_id'] == '3958' || $value['product_id'] == '3959' ||  $value['product_id'] == '3960' ||  $value['product_id'] == '3961' ||  $value['product_id'] == '3962' ||  $value['product_id'] == '3963') {

											$params = array($rule_ids, $couponData['discount_amount'], $perItemDiscount, $total_discount_amount, time(), $item_id, $quote_id);

											$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ?,updated_at=? WHERE item_id = ? AND quote_id= ?", $params);

											// if ($variants['0']['Subscription'] == '1 Year Print + 1 Year Digital' || $variants['0']['Subscription'] == '2 Year Print + 2 Year Digital' || $variants['0']['Subscription'] == '3 Year Print + 3 Year Digital') {

											// }



											// $params1 = array($Adding_back_shipping_charges, $Adding_back_shipping_charges, time(), $quote_id);

											// $update_row1 = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET grand_total=?,base_grand_total=?, updated_at = ?  WHERE quote_id=?", $params1);

										}

										if ($variants['0']['Subscription'] == '1 Year' || $variants[0]['Subscription'] == '1 Years' || $variants[0]['Subscription'] == '1 Year Print') {

											// echo "h1";

											$params = array($rule_ids, $couponData['discount_amount'], $perItemDiscount, $total_discount_amount, time(), $item_id, $quote_id);

											$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ?,updated_at=? WHERE item_id = ? AND quote_id= ?", $params);



											// $params1 = array($Adding_back_shipping_charges, $Adding_back_shipping_charges, time(), $quote_id);

											// $update_row1 = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET grand_total=?,base_grand_total=?, updated_at = ?  WHERE quote_id=?", $params1);



											// $params1 = array($Adding_back_shipping_charges, time(), $quote_id);

											// $update_row1 = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET grand_total=?, updated_at = ?  WHERE quote_id=?", $params);

										}

									}



									if ($couponData['rule_id'] == '149') {

										$variants = json_decode($value['product_variants'], true);

										// print_r($variants['0']['Subscription']);



										if ($value['product_id'] == '3958' || $value['product_id'] == '3959' ||  $value['product_id'] == '3960' ||  $value['product_id'] == '3961' ||  $value['product_id'] == '3962' ||  $value['product_id'] == '3963') {

											$params = array($rule_ids, $couponData['discount_amount'], $perItemDiscount, $total_discount_amount, time(), $item_id, $quote_id);

											$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ?,updated_at=? WHERE item_id = ? AND quote_id= ?", $params);

											// if ($variants['0']['Subscription'] == '1 Year Print + 1 Year Digital' || $variants['0']['Subscription'] == '2 Year Print + 2 Year Digital' || $variants['0']['Subscription'] == '3 Year Print + 3 Year Digital') {

											// }



											// $params1 = array($Adding_back_shipping_charges, $Adding_back_shipping_charges, time(), $quote_id);

											// $update_row1 = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET grand_total=?,base_grand_total=?, updated_at = ?  WHERE quote_id=?", $params1);

										}

										if ($variants['0']['Subscription'] == '1 Years' || $variants['0']['Subscription'] == '1 Year' || $variants['0']['Subscription'] == '6 Months') {

											// echo "h1";

											$params = array($rule_ids, $couponData['discount_amount'], $perItemDiscount, $total_discount_amount, time(), $item_id, $quote_id);

											$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ?,updated_at=? WHERE item_id = ? AND quote_id= ?", $params);



											// $params1 = array($Adding_back_shipping_charges, $Adding_back_shipping_charges, time(), $quote_id);

											// $update_row1 = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET grand_total=?,base_grand_total=?, updated_at = ?  WHERE quote_id=?", $params1);



											// $params1 = array($Adding_back_shipping_charges, time(), $quote_id);

											// $update_row1 = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET grand_total=?, updated_at = ?  WHERE quote_id=?", $params);

										}

									}





									if ($this->dbl->dbl_conn->getLastErrno() === 0) {

										$status_flag = true;

									} else {



										$status_flag = false;

									}

								}

							}

							// die;

						} else {







							$status_flag = false;

						}

					}

					/*------------------------------------End------------------------------------*/

				} else if ($couponData['type'] == 3 || $couponData['type'] == 4) {







					$usge_per_coupon = $couponData['usge_per_coupon'];



					$usage_per_customer = $couponData['usage_per_customer'];



					if ($couponData['apply_condition'] == 'discount_on_mincartval') {



						if ($couponData['min_cart_value'] != '' && ($base_subtotal < $couponData['min_cart_value'])) {



							if ($flag == 'apply_btn') {



								$status_flag = false;

							} else {



								$this->removeCouponCode($quote_id, $couponData['coupon_code'], $couponData['coupon_type']);



								$status_flag = true;

							}

						} else if ($couponData['type'] == 4) {





							if ($customer_id > 0 && ($usage_per_customer != 0 && $usage_per_customer > 0)) {



								$CouponUsedInOrderByCustomer = $this->getCouponUsedCountInOrders($couponData['coupon_code'], $couponData['coupon_type'], $customer_id);



								if ($CouponUsedInOrderByCustomer != false) {



									$order_usge_per_customer = (isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer > 0) ? $CouponUsedInOrderByCustomer : 0;

								} else {



									$order_usge_per_customer = 0;

								}





								if (($order_usge_per_customer > 0) && ($order_usge_per_customer >= $usage_per_customer)) {

									$status_flag = false;

								} else {



									if ($couponData['apply_type'] == 'by_percent') {



										$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));



										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



										$discount_percent = $couponData['discount_amount'];



										$update_cart_item = $this->updateCartItemsCouponDiscount($quote_id, $rule_ids, $discount_percent, $cartItems);



										$status_flag = true;

									} else if ($couponData['apply_type'] == 'by_fixed') {



										$disc_amount = $couponData['discount_amount'];



										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



										$discount_percent = 0;



										$status_flag = true;

									}

								}

							} else {





								if ($couponData['apply_type'] == 'by_percent') {



									$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));



									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



									$discount_percent = $couponData['discount_amount'];



									$update_cart_item = $this->updateCartItemsCouponDiscount($quote_id, $rule_ids, $discount_percent, $cartItems);



									$status_flag = true;

								} else if ($couponData['apply_type'] == 'by_fixed') {



									$disc_amount = $couponData['discount_amount'];



									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



									$discount_percent = 0;



									$status_flag = true;

								}

							}

						} else if ($couponData['type'] == 3 && $couponData['usge_per_coupon'] != 0 && $couponData['usge_per_coupon'] > 0) {



							$CouponUsedInOrder = $this->getCouponUsedCountInOrders($couponData['coupon_code'], $couponData['coupon_type']);



							if ($usge_per_coupon != 0 || $usge_per_coupon > 0) {



								if ($CouponUsedInOrder != false) {



									$order_usge_per_coupon = (isset($CouponUsedInOrder) && $CouponUsedInOrder > 0) ? $CouponUsedInOrder : 0;

								} else {



									$order_usge_per_coupon = 0;

								}



								if (($order_usge_per_coupon > 0) && ($order_usge_per_coupon >= $usge_per_coupon)) {



									$status_flag = false;

								} else {



									if ($customer_id > 0 && ($usage_per_customer != 0 && $usage_per_customer > 0)) {



										$CouponUsedInOrderByCustomer = $this->getCouponUsedCountInOrders($couponData['coupon_code'], $couponData['coupon_type'], $customer_id);



										if ($CouponUsedInOrderByCustomer != false) {



											$order_usge_per_customer = (isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer > 0) ? $CouponUsedInOrderByCustomer : 0;

										} else {



											$order_usge_per_customer = 0;

										}



										if (($order_usge_per_customer > 0) && ($order_usge_per_customer >= $usage_per_customer)) {



											$status_flag = false;

										} else {



											if ($couponData['apply_type'] == 'by_percent') {



												$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));



												$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



												$discount_percent = $couponData['discount_amount'];



												$update_cart_item = $this->updateCartItemsCouponDiscount($quote_id, $rule_ids, $discount_percent, $cartItems);



												$status_flag = true;

											} else if ($couponData['apply_type'] == 'by_fixed') {



												$disc_amount = $couponData['discount_amount'];



												$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



												$discount_percent = 0;



												$status_flag = true;

											}

										}

									} else {



										if ($couponData['apply_type'] == 'by_percent') {



											$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));



											$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



											$discount_percent = $couponData['discount_amount'];



											$update_cart_item = $this->updateCartItemsCouponDiscount($quote_id, $rule_ids, $discount_percent, $cartItems);



											$status_flag = true;

										} else if ($couponData['apply_type'] == 'by_fixed') {



											$disc_amount = $couponData['discount_amount'];



											$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



											$discount_percent = 0;



											$status_flag = true;

										}

									}

								}

							}

						} else  if (($couponData['type'] == 3) && ($couponData['usge_per_coupon'] == 0) && ($usage_per_customer != 0 && $usage_per_customer > 0)) {



							if ($customer_id > 0 && ($usage_per_customer != 0 && $usage_per_customer > 0)) {



								$CouponUsedInOrderByCustomer = $this->getCouponUsedCountInOrders($couponData['coupon_code'], $couponData['coupon_type'], $customer_id);



								if ($CouponUsedInOrderByCustomer != false) {



									$order_usge_per_customer = (isset($CouponUsedInOrderByCustomer) && $CouponUsedInOrderByCustomer > 0) ? $CouponUsedInOrderByCustomer : 0;

								} else {



									$order_usge_per_customer = 0;

								}



								if (($order_usge_per_customer > 0) && ($order_usge_per_customer >= $usage_per_customer)) {



									$status_flag = false;

								} else {



									if ($couponData['apply_type'] == 'by_percent') {



										$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));



										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



										$discount_percent = $couponData['discount_amount'];



										$update_cart_item = $this->updateCartItemsCouponDiscount($quote_id, $rule_ids, $discount_percent, $cartItems);



										$status_flag = true;

									} else if ($couponData['apply_type'] == 'by_fixed') {



										$disc_amount = $couponData['discount_amount'];



										$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



										$discount_percent = 0;



										$status_flag = true;

									}

								}

							} else {



								if ($couponData['apply_type'] == 'by_percent') {



									$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));



									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



									$discount_percent = $couponData['discount_amount'];



									$update_cart_item = $this->updateCartItemsCouponDiscount($quote_id, $rule_ids, $discount_percent, $cartItems);



									$status_flag = true;

								} else if ($couponData['apply_type'] == 'by_fixed') {



									$disc_amount = $couponData['discount_amount'];



									$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;



									$discount_percent = 0;



									$status_flag = true;

								}

							}

						} else {



							/*--------final else--------------------------------------------*/



							if ($couponData['apply_type'] == 'by_percent') {







								$disc_amount = ($base_subtotal * ($couponData['discount_amount'] / 100));







								$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







								$discount_percent = $couponData['discount_amount'];















								$update_cart_item = $this->updateCartItemsCouponDiscount($quote_id, $rule_ids, $discount_percent, $cartItems);







								$status_flag = true;

							} else if ($couponData['apply_type'] == 'by_fixed') {







								$disc_amount = $couponData['discount_amount'];







								$discount_amount = ($disc_amount > $base_subtotal) ? $base_subtotal : $disc_amount;







								$discount_percent = 0;







								$status_flag = true;

							}







							/*-----------------Final else----------------------*/

						}

					}







					/*---------------------------------------------*/

				}

			}







			if ($status_flag == true) {







				$update_quote = $this->UpateQuoteCouponDiscount($quote_id, $rule_ids, $couponData['coupon_code'], $discount_amount, $discount_percent, $couponData['coupon_type']);







				return true;

			} else {







				return false;

			}

		} else {







			return false;

		}

	}







	public function addCouponDiscount($QuoteData)

	{



		if ($QuoteData['coupon_code'] != '') {



			$coupon_type = 0;



			$couponData = $this->getCouponData($QuoteData['coupon_code'], $coupon_type, $QuoteData['customer_id']);



			$cartData = $this->getCartListing($QuoteData['session_id']);

			if ($couponData['coupon_code'] == 'WELCOMEBACK04') {



				foreach ($cartData['cartItems'] as $item) {



					$variants = json_decode($item['product_variants'], true);



					if ($item['product_id'] == '3958' || $item['product_id'] == '3959' ||  $item['product_id'] == '3960' ||  $item['product_id'] == '3961' ||  $item['product_id'] == '3962' ||  $item['product_id'] == '3963') {

						$this->updateCouponDiscountNew($item['quote_id'], $couponData, $cartData, 'apply_btn');

						// if ($variants['0']['Subscription'] == '1 Year Print + 1 Year Digital' || $variants['0']['Subscription'] == '2 Year Print + 2 Year Digital' || $variants['0']['Subscription'] == '3 Year Print + 3 Year Digital') {

						// }

					}



					if ($variants['0']['Subscription'] == '1 Year' || $variants[0]['Subscription'] == '1 Years' || $variants[0]['Subscription'] == '1 Year Print') {

						// echo "hii1";

						// die;

						$this->updateCouponDiscountNew($item['quote_id'], $couponData, $cartData, 'apply_btn');

					}

				}

			}else if ($couponData['coupon_code'] == 'IMINT005') {



				foreach ($cartData['cartItems'] as $item) {



					$variants = json_decode($item['product_variants'], true);



					if ($item['product_id'] == '3958' || $item['product_id'] == '3959' ||  $item['product_id'] == '3960' ||  $item['product_id'] == '3961' ||  $item['product_id'] == '3962' ||  $item['product_id'] == '3963') {

					// 	if ($variants['0']['Subscription'] == '1 Time (1 Issues)' || $variants['0']['Subscription'] == '3 Months (3 Issues)' || $variants['0']['Subscription'] == '3 Months' || $variants['0']['Subscription'] == '1 Time') {

					// 	$error = 'This ' . $coupon_label . ' code not applicable for you.';

	

					// }

					$this->updateCouponDiscountNew($item['quote_id'], $couponData, $cartData, 'apply_btn');



					

					}



					if ($variants['0']['Subscription'] == '1 Years' || $variants['0']['Subscription'] == '1 Year' || $variants['0']['Subscription'] == '6 Months') {

						$this->updateCouponDiscountNew($item['quote_id'], $couponData, $cartData, 'apply_btn');

	

					}

					

				}

			}  else {

				// echo "h2";

				// exit();

				$this->updateCouponDiscount($QuoteData['quote_id'], $couponData, $cartData);

			}

		}















		if ($QuoteData['voucher_code'] != '') {







			$coupon_type = 1;















			$couponData = $this->getCouponData($QuoteData['voucher_code'], $coupon_type, $QuoteData['customer_id']);







			$cartData = $this->getCartListing($QuoteData['session_id']);







			$this->updateCouponDiscount($QuoteData['quote_id'], $couponData, $cartData);

		}

	}



	public function UpateQuoteTotal($quote_id, $remove_coupon = '')

	{







		// echo "<pre>";

		// print_r($AllQuoteItems);

		// die;



		$QuoteData = $this->getQuoteDataById($quote_id);

		

		$coupon_code = $QuoteData['coupon_code'];

		$total_grand_total = 0;



		$total_discount_amount = 0;  // Initialize a variable to store the sum of discount amounts

		$new_shipping_amount = 0;

		$AllQuoteItems = $this->getQuoteItems($quote_id);

		foreach ($AllQuoteItems as $item) {

			// echo "hii";

			// Check if the size of the array is more than 1 before performing updates and calculations

			$getProduct = $this->getProduct($item['product_id']);

			$getCategory = $this->getCategory($item['parent_product_id']);

			// print_R($getCategory);

			// die();

			if (count($AllQuoteItems) > 1) {

				$total_discount_amount += $item['total_discount_amount'];

				if ($item['parent_product_id'] == 722 || $item['parent_product_id'] == 4453 || $item['parent_product_id'] == 4458 || $item['parent_product_id'] == 3777 || $item['parent_product_id'] == 4465 || $item['parent_product_id'] == 4470 || $item['parent_product_id'] == 411 || $item['parent_product_id'] == 4477 || $item['parent_product_id'] == 4482 || $item['parent_product_id'] == 4489 || $item['parent_product_id'] == 4494 || $item['parent_product_id'] == 3777 || $item['parent_product_id'] == 675 || $item['parent_product_id'] == 3914 || $item['parent_product_id'] == 3388){

					if ($item['qty_ordered'] > 1) {

						$new_shipping_amount += $getProduct['shipping_amount'] * $item['qty_ordered'];

					} else {

						$new_shipping_amount += $getProduct['shipping_amount'];

					}

				}

				if ($getCategory) {

					if ($getCategory['category_ids'] == '8') { // Check for category OR product_id

						// $getProduct = $this->getProduct($item['product_id']);

						if ($item['qty_ordered'] > 1) {

							$new_shipping_amount += $getProduct['shipping_amount'] * $item['qty_ordered'];

						} else {

							$new_shipping_amount += $getProduct['shipping_amount'];

						}

					}

				}

			} else { 

				if ($item['parent_product_id'] == 722 || $item['parent_product_id'] == 4453 || $item['parent_product_id'] == 4458 || $item['parent_product_id'] == 3777 || $item['parent_product_id'] == 4465 || $item['parent_product_id'] == 4470 || $item['parent_product_id'] == 411 || $item['parent_product_id'] == 4477 || $item['parent_product_id'] == 4482 || $item['parent_product_id'] == 4489 || $item['parent_product_id'] == 4494 || $item['parent_product_id'] == 3777 || $item['parent_product_id'] == 675 || $item['parent_product_id'] == 3914 || $item['parent_product_id'] == 3388){

					if ($item['qty_ordered'] > 1) {

						$new_shipping_amount = $getProduct['shipping_amount'] * $item['qty_ordered'];

					} else {

						$new_shipping_amount = $getProduct['shipping_amount'];

					}

				}

				if ($getCategory) {

					if ($getCategory['category_ids'] == '8') { // Check for category OR product_id

						// $getProduct = $this->getProduct($item['product_id']);

						if ($item['qty_ordered'] > 1) {

							$new_shipping_amount = $getProduct['shipping_amount'] * $item['qty_ordered'];

						} else {

							$new_shipping_amount = $getProduct['shipping_amount'];

						}

					}

				}

				$total_discount_amount = $item['total_discount_amount']; 

			}

			

			$applied_rule_ids = $item['applied_rule_ids'];

			$discount_percent = $item['discount_percent'];

			// print_R($new_shipping_amount);

			// die();

			$params = array($total_discount_amount, $total_discount_amount, $applied_rule_ids, $discount_percent, $new_shipping_amount, time(), $quote_id);

			$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET base_discount_amount=?, discount_amount=?, applied_rule_ids=?,discount_percent =?,shipping_amount=?, updated_at = ? WHERE quote_id=?", $params);

		}

		$TotalInfo = $this->getTotalByQuote($quote_id);

		// Retrieve sale quote items data

		$sale_quote_items_data = $this->get_sale_quote_items_data($quote_id);

		// echo "<pre>";

		// print_r($sale_quote_items_data);

		// die;

		$base_subtotal = 0;



		// Check if there are multiple items

		foreach ($sale_quote_items_data as $items) {

			if (count($sale_quote_items_data) > 1) {

				$base_subtotal += $items['total_price'];

			} else {

				$base_subtotal = $items['total_price'];

			}

			// Handle the case where there are no items

		}

		// print_r($base_subtotal);

		// die;

		// echo "Base Subtotal: $base_subtotal<br>";



		// echo "<pre>";

		// print_r($base_subtotal);

		// die;

		$TotalQtyInfo = $this->getTotalQuantityByQuote($quote_id);

		$discount_amount = $this->get_discount_amount($quote_id);

		$voucher_amount = $this->get_vocher_amount($quote_id);

		$new_sale_quote_data = $this->get_shipping_amount($quote_id);

		

		// $new_sale_vocher_data = $this->get_sale_vocher_data($new_sale_quote_data['voucher_code']);

		// $new_sale_coupon_data = $this->get_sale_coupon_data($new_sale_vocher_data['rule_id']);



		$total_qty = (isset($TotalQtyInfo['total_qty']) && $TotalQtyInfo['total_qty'] > 0) ? $TotalQtyInfo['total_qty'] : 0;

		// echo "<pre>";

		// print_r($total_qty);

		// die;

		if ($discount_amount['base_discount_amount'] != 0) {

			$total_after_discount_deduction = $base_subtotal - $discount_amount['base_discount_amount'];

		} else if ($voucher_amount['voucher_amount'] != 0) {

			$total_after_discount_deduction = $base_subtotal - $voucher_amount['voucher_amount'];

		} else {

			$total_after_discount_deduction = $base_subtotal;

		}



		$subtotal = $total_after_discount_deduction + $new_sale_quote_data['shipping_amount'];

		$grand_total = $subtotal;

		// print_r($grand_total);

		// die;



		$QuoteTotalTaxamount = $this->calculateQuoteTaxAmout($quote_id, $AllQuoteItems);



		$tax_amount = $QuoteTotalTaxamount;



		$params = array($total_qty, $base_subtotal, $subtotal, $base_subtotal, $grand_total, $tax_amount, $tax_amount, $coupon_code, time(), $quote_id);



		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET total_qty_ordered=?, base_subtotal=?, subtotal=?, base_grand_total=?, grand_total=?, base_tax_amount=?, tax_amount=?,coupon_code=?, updated_at = ?  WHERE quote_id=?", $params);



		if ($this->dbl->dbl_conn->getLastErrno() === 0) {



			if ($this->dbl->dbl_conn->count > 0) {

				return $update_row;

			} else {



				return false;

			}

		} else {



			exit;



			return false;

		}

	}



	public function UpateQuoteTotalNew($quote_id, $remove_coupon = '')

	{



		$QuoteData = $this->getQuoteDataById($quote_id);



		$coupon_code = $QuoteData['coupon_code'];



		$total_grand_total = 0;

		$new_shipping_amount = 0;

		$AllQuoteItems = $this->getQuoteItems($quote_id);

		if ($coupon_code == 'WELCOMEBACK04' || $coupon_code == 'IMINT005') {



			$total_grand_total = 0;  // Initialize a variable to store the sum of grand totals

			$total_discount_amount = 0;  // Initialize a variable to store the sum of discount amounts



			foreach ($AllQuoteItems as $item) {

				$variants = json_decode($item['product_variants'], true);



				if (count($AllQuoteItems) > 1) {



					// $getProduct = $this->getProduct($item['product_id']);

					$getProduct = $this->getProduct($item['product_id']);

					$getCategory = $this->getCategory($item['parent_product_id']);

					$total_discount_amount += $item['total_discount_amount'];



					// if ($item['qty_ordered'] > 1) {

					// 	$new_shipping_amount += $getProduct['shipping_amount'] * $item['qty_ordered'];

					// } else {

					// 	$new_shipping_amount += $getProduct['shipping_amount'];

					// }

					if ($getCategory){

						if ($getCategory['category_ids'] == '8') {

							// $getProduct = $this->getProduct($item['product_id']);

							if ($item['qty_ordered'] > 1) {

								$new_shipping_amount += $getProduct['shipping_amount'] * $item['qty_ordered'];

							} else {

								$new_shipping_amount += $getProduct['shipping_amount'];

							}

						}

					}

					// $total_discount_amount = $item['total_discount_amount']; 



					// if ($item['product_id'] == '3958' || $item['product_id'] == '3959' || $item['product_id'] == '3960' || $item['product_id'] == '3961' || $item['product_id'] == '3962' || $item['product_id'] == '3963') {

					// 	$base_discount_amount = $item['discount_amount'];

					$applied_rule_ids = $item['applied_rule_ids'];

					$discount_percent = $item['discount_percent'];



					// }

					$params = array($total_discount_amount, $total_discount_amount, $applied_rule_ids, $discount_percent, $new_shipping_amount, time(), $quote_id);

					$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET base_discount_amount=?, discount_amount=?, applied_rule_ids=?,discount_percent =?,shipping_amount=?, updated_at = ? WHERE quote_id=?", $params);



					// Process items with specific subscription

					// if (isset($variants[0]['Subscription']) && $variants[0]['Subscription'] == '1 Year' || $variants[0]['Subscription'] == '1 Years' || $variants[0]['Subscription'] == '1 Year Print') {

					// 	$base_discount_amount = $item['discount_amount'];

					// 	$applied_rule_ids = $item['applied_rule_ids'];

					// 	$discount_percent = $item['discount_percent'];



					// 	$params = array($total_discount_amount, $total_discount_amount, $applied_rule_ids, $discount_percent, $new_shipping_amount, time(), $quote_id);

					// 	$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET base_discount_amount=?, discount_amount=?, applied_rule_ids=?,discount_percent =?,shipping_amount=?, updated_at = ? WHERE quote_id=?", $params);

					// }

				} else {

					// Get product details

					$getProduct = $this->getProduct($item['product_id']);

					$getCategory = $this->getCategory($item['parent_product_id']);



					$total_discount_amount = $item['total_discount_amount']; 



					// if ($item['qty_ordered'] > 1) {

					// 	$new_shipping_amount = $getProduct['shipping_amount'] * $item['qty_ordered'];

					// } else {

					// 	$new_shipping_amount = $getProduct['shipping_amount'];

					// }

					

					if ($getCategory){

						if ($getCategory['category_ids'] == '8') {

							// $getProduct = $this->getProduct($item['product_id']);

							if ($item['qty_ordered'] > 1) {

								$new_shipping_amount = $getProduct['shipping_amount'] * $item['qty_ordered'];

							} else {

								$new_shipping_amount = $getProduct['shipping_amount'];

							}

						}

					}

					$applied_rule_ids = $item['applied_rule_ids'];

					$discount_percent = $item['discount_percent'];



					$params = array($total_discount_amount, $total_discount_amount, $applied_rule_ids, $discount_percent, $new_shipping_amount, time(), $quote_id);

					$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET base_discount_amount=?, discount_amount=?, applied_rule_ids=?,discount_percent =?,shipping_amount=?, updated_at = ? WHERE quote_id=?", $params);

					// if ($item['product_id'] == '3958' || $item['product_id'] == '3959' || $item['product_id'] == '3960' || $item['product_id'] == '3961' || $item['product_id'] == '3962' || $item['product_id'] == '3963') {

					// 	$base_discount_amount = $item['discount_amount'];

					// }



					// // Process items with specific subscription

					// if (isset($variants[0]['Subscription']) && $variants[0]['Subscription'] == '1 Year' || $variants[0]['Subscription'] == '1 Years' || $variants[0]['Subscription'] == '1 Year Print') {

					// 	$base_discount_amount = $item['discount_amount'];

					// 	$applied_rule_ids = $item['applied_rule_ids'];

					// 	$discount_percent = $item['discount_percent'];



					// 	$params = array($total_discount_amount, $total_discount_amount, $applied_rule_ids, $discount_percent, $new_shipping_amount, time(), $quote_id);

					// 	$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET base_discount_amount=?, discount_amount=?, applied_rule_ids=?,discount_percent =?,shipping_amount=?, updated_at = ? WHERE quote_id=?", $params);

					// }

				}

			}

		}



		$TotalInfo = $this->getTotalByQuote($quote_id);

		// Retrieve sale quote items data

		$sale_quote_items_data = $this->get_sale_quote_items_data($quote_id);

		// echo "<pre>";

		// print_r($sale_quote_items_data);

		// die;

		$base_subtotal = 0;



		// Check if there are multiple items

		foreach ($sale_quote_items_data as $items) {

			if (count($sale_quote_items_data) > 1) {

				$base_subtotal += $items['total_price'];

			} else {

				$base_subtotal = $items['total_price'];

			}

			// Handle the case where there are no items

		}

		// print_r($base_subtotal);

		// die;

		// echo "Base Subtotal: $base_subtotal<br>";



		// echo "<pre>";

		// print_r($base_subtotal);

		// die;

		$TotalQtyInfo = $this->getTotalQuantityByQuote($quote_id);

		$discount_amount = $this->get_discount_amount($quote_id);

		$voucher_amount = $this->get_vocher_amount($quote_id);

		$new_sale_quote_data = $this->get_shipping_amount($quote_id);





		// $new_sale_vocher_data = $this->get_sale_vocher_data($new_sale_quote_data['voucher_code']);

		// $new_sale_coupon_data = $this->get_sale_coupon_data($new_sale_vocher_data['rule_id']);



		$total_qty = (isset($TotalQtyInfo['total_qty']) && $TotalQtyInfo['total_qty'] > 0) ? $TotalQtyInfo['total_qty'] : 0;

		// echo "<pre>";

		// print_r($total_qty);

		// die;

		if ($discount_amount['base_discount_amount'] != 0) {

			$total_after_discount_deduction = $base_subtotal - $discount_amount['base_discount_amount'];

		} else if ($voucher_amount['voucher_amount'] != 0) {

			$total_after_discount_deduction = $base_subtotal - $voucher_amount['voucher_amount'];

		} else {

			$total_after_discount_deduction = $base_subtotal;

		}



		$subtotal = $total_after_discount_deduction + $new_sale_quote_data['shipping_amount'];

		$grand_total = $subtotal;

		// print_r($grand_total);

		// die;



		$QuoteTotalTaxamount = $this->calculateQuoteTaxAmout($quote_id, $AllQuoteItems);



		$tax_amount = $QuoteTotalTaxamount;



		$params = array($total_qty, $base_subtotal, $subtotal, $base_subtotal, $grand_total, $tax_amount, $tax_amount, $coupon_code, time(), $quote_id);



		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET total_qty_ordered=?, base_subtotal=?, subtotal=?, base_grand_total=?, grand_total=?, base_tax_amount=?, tax_amount=?,coupon_code=?, updated_at = ?  WHERE quote_id=?", $params);



		if ($this->dbl->dbl_conn->getLastErrno() === 0) {



			if ($this->dbl->dbl_conn->count > 0) {

				return $update_row;

			} else {



				return false;

			}

		} else {



			exit;



			return false;

		}

	}



	public function getProduct($product_id)

	{



		$sql = "SELECT shipping_amount,parent_id FROM products WHERE id = $product_id";



		// Execute the query

		$row = $this->dbl->dbl_conn->rawQueryOne($sql);



		// Check for errors in the last query

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {

			// Check if any rows were returned

			if ($this->dbl->dbl_conn->count > 0) {

				return $row;

			} else {

				echo "No rows found.<br>";

				return false;

			}

		} else {

			echo "Database error: " . $this->dbl->dbl_conn->getLastError() . "<br>";

			return false;

		}

	}



	public function getCategory($product_id)

	{

		// print_R($product_id);

		// die();

		$sql = "SELECT * FROM products_category WHERE product_id = $product_id and category_ids = '8'";

		// echo $sql;die;

		// Execute the query

		$row = $this->dbl->dbl_conn->rawQueryOne($sql);



		// Check for errors in the last query

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {

			// Check if any rows were returned

			if ($this->dbl->dbl_conn->count > 0) {

				return $row;

			} else {

				// echo "No rows found.<br>";

				return false;

			}

		} else {

			echo "Database error: " . $this->dbl->dbl_conn->getLastError() . "<br>";

			return false;

		}

	}



	public function get_discount_amount($quote_id)

	{



		$sql = "SELECT base_discount_amount FROM sales_quote WHERE quote_id = $quote_id";



		// Execute the query

		$row = $this->dbl->dbl_conn->rawQueryOne($sql);



		// Check for errors in the last query

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {

			// Check if any rows were returned

			if ($this->dbl->dbl_conn->count > 0) {

				return $row;

			} else {

				echo "No rows found.<br>";

				return false;

			}

		} else {

			echo "Database error: " . $this->dbl->dbl_conn->getLastError() . "<br>";

			return false;

		}

	}



	public function get_vocher_amount($quote_id)

	{



		$sql = "SELECT voucher_amount FROM sales_quote WHERE quote_id = $quote_id";



		// Execute the query

		$row = $this->dbl->dbl_conn->rawQueryOne($sql);



		// Check for errors in the last query

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {

			// Check if any rows were returned

			if ($this->dbl->dbl_conn->count > 0) {

				return $row;

			} else {

				echo "No rows found.<br>";

				return false;

			}

		} else {

			echo "Database error: " . $this->dbl->dbl_conn->getLastError() . "<br>";

			return false;

		}

	}



	public function get_shipping_amount($quote_id)

	{



		$sql = "SELECT * FROM sales_quote WHERE quote_id = $quote_id";



		// Execute the query

		$row = $this->dbl->dbl_conn->rawQueryOne($sql);



		// Check for errors in the last query

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {

			// Check if any rows were returned

			if ($this->dbl->dbl_conn->count > 0) {

				return $row;

			} else {

				echo "No rows found.<br>";

				return false;

			}

		} else {

			echo "Database error: " . $this->dbl->dbl_conn->getLastError() . "<br>";

			return false;

		}

	}

	public function get_sale_vocher_data($voucher_code)

	{



		$sql = "SELECT * FROM salesrule_coupon WHERE coupon_code = '$voucher_code'";

		// echo $sql;

		// die;

		// Execute the query

		$row = $this->dbl->dbl_conn->rawQueryOne($sql);



		// Check for errors in the last query

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {

			// Check if any rows were returned

			if ($this->dbl->dbl_conn->count > 0) {

				return $row;

			} else {

				echo "No rows found.<br>";

				return false;

			}

		} else {

			echo "Database error: " . $this->dbl->dbl_conn->getLastError() . "<br>";

			return false;

		}

	}



	public function get_sale_coupon_data($rule_id)

	{



		$sql = "SELECT * FROM salesrule WHERE rule_id = '$rule_id'";

		// echo $sql;

		// die;

		// Execute the query

		$row = $this->dbl->dbl_conn->rawQueryOne($sql);



		// Check for errors in the last query

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {

			// Check if any rows were returned

			if ($this->dbl->dbl_conn->count > 0) {

				return $row;

			} else {

				echo "No rows found.<br>";

				return false;

			}

		} else {

			echo "Database error: " . $this->dbl->dbl_conn->getLastError() . "<br>";

			return false;

		}

	}



	public function get_sale_quote_items_data($quote_id)

	{



		$sql = "SELECT * FROM sales_quote_items WHERE quote_id = '$quote_id'";

		// echo $sql;

		// die;

		// Execute the query

		$row = $this->dbl->dbl_conn->rawQuery($sql);



		// Check for errors in the last query

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {

			// Check if any rows were returned

			if ($this->dbl->dbl_conn->count > 0) {

				return $row;

			} else {

				echo "No rows found.<br>";

				return false;

			}

		} else {

			echo "Database error: " . $this->dbl->dbl_conn->getLastError() . "<br>";

			return false;

		}

	}



	public function getCartListing($session_id, $quote_id = '', $customer_id = '')



	{



		return (new GetCartListing())($session_id, $quote_id, $customer_id);

	}











	function getCartCountByQuoteId($quote_id, $customer_id = '')

	{







		if (empty($customer_id)) {















			$params = array($quote_id);







			$row  = $this->dbl->dbl_conn->rawQueryOne("SELECT count(sqi.item_id) as total_count FROM sales_quote_items  as sqi LEFT JOIN sales_quote as sq ON sqi.quote_id = sq.quote_id where sq.quote_id =  ?  ", $params);

		} else {















			$params = array($quote_id, $customer_id);





			// print_r($params);

			// echo "SELECT count(sqi.item_id) as total_count FROM sales_quote_items as sqi LEFT JOIN sales_quote as sq ON sqi.quote_id = sq.quote_id where (sq.quote_id = ?  AND sq.customer_id= ? )";die();

			$row  =   $this->dbl->dbl_conn->rawQueryOne("SELECT count(sqi.item_id) as total_count FROM sales_quote_items as sqi LEFT JOIN sales_quote as sq ON sqi.quote_id = sq.quote_id where (sq.quote_id = ?  AND sq.customer_id= ? )", $params);

		}















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















	function getCartCountBySessionId($session_id, $customer_id = '')

	{







		if (empty($customer_id)) {















			$params = array($session_id);







			$row  = $this->dbl->dbl_conn->rawQueryOne("SELECT count(sqi.item_id) as total_count FROM sales_quote_items as sqi LEFT JOIN sales_quote as sq ON sqi.quote_id = sq.quote_id where sq.session_id =  ?  ", $params);

		} else {















			$params = array($session_id, $customer_id);







			$row  = $this->dbl->dbl_conn->rawQueryOne("SELECT count(sqi.item_id) as total_count FROM sales_quote_items as sqi LEFT JOIN sales_quote as sq ON sqi.quote_id = sq.quote_id where (sq.session_id = ?  AND sq.customer_id= ? )", $params);

		}







		//$row  = $this->dbl->dbl_conn->rawQueryOne($sql);







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















	function removeCartItem($quote_id, $item_id)

	{























		$params = array($quote_id, $item_id);







		$delete_row = $this->dbl->dbl_conn->rawQueryOne("DELETE FROM sales_quote_items where quote_id= ? AND item_id = ?", $params);















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















	function updateCartItem($item_id, $quote_id, $qty)

	{







		$itemData = $this->getQuoteItemDataById($item_id);















		if ($this->dbl->dbl_conn->getLastErrno() === 0) {







			if ($this->dbl->dbl_conn->count > 0) {







				$price = $itemData['price'];







				$tax_percent = $itemData['tax_percent'];







				$tax_amount = $itemData['tax_amount'];















				$totalPrice = $price * $qty;







				$params = array($qty, $totalPrice, $tax_percent, $tax_amount, time(), $item_id, $quote_id);







				$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET qty_ordered = ?, total_price = ?, tax_percent = ?, tax_amount = ?, updated_at = ? WHERE item_id = ? AND quote_id= ?", $params);















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

			} else {







				return false;

			}

		} else {







			return false;

		}

	}







	function updateWholeCartItems($quote_id, $cart_items)

	{















		$items = json_decode($cart_items);







		if (is_array($items) && count($items) > 0) {







			foreach ($items as $value) {







				$item_id = $value->item_id;







				$qty = $value->qty;







				$itemData = $this->getQuoteItemDataById($item_id);







				if ($this->dbl->dbl_conn->getLastErrno() === 0) {







					if ($this->dbl->dbl_conn->count > 0) {







						$price = $itemData['price'];







						$totalPrice = $price * $qty;







						$tax_percent = $itemData['tax_percent'];







						$tax_amount = $itemData['tax_amount'];















						$params = array($qty, $totalPrice, $tax_percent, $tax_amount, time(), $item_id, $quote_id);







						$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET qty_ordered = ?, total_price = ?, tax_percent = ?, tax_amount = ?, updated_at = ? WHERE item_id = ? AND quote_id= ?", $params);















						if ($this->dbl->dbl_conn->getLastErrno() === 0) {







							$flag = true;

						} else {







							$flag = false;

						}

					} else {







						$flag = false;

					}

				} else {







					$flag = false;

				}

			}







			return $flag;

		} else {







			return false;

		}

	}















	public function checkQuoteItemDataExistById($quote_id, $product_id, $parent_product_id = '')







	{







		if (isset($parent_product_id) && $parent_product_id != '') {







			$sql =  "SELECT * FROM sales_quote_items where `product_id` = '$product_id' AND parent_product_id='$parent_product_id'  AND quote_id = $quote_id ";

		} else {







			$sql =  "SELECT * FROM sales_quote_items where `product_id` = '$product_id' AND quote_id = $quote_id ";

		}







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















	public function checkQuoteItemDataExistInOtherBundle($quote_id, $bundle_product_id = '', $product_id = '', $parent_product_id = '')







	{























		$parent_product_id = ($parent_product_id != '') ? $parent_product_id : 0;















		$where_bundle_child = '"parent_id":' . $parent_product_id . ',"product_id":' . $product_id;







		$params = array($bundle_product_id, $quote_id);















		$add_query = '';







		if ($bundle_product_id != '') {







			$add_query = "AND product_id != $bundle_product_id";

		}















		$sql = "SELECT product_id FROM sales_quote_items where product_type = 'bundle' " . $add_query . " AND bundle_child_details like '%" . $where_bundle_child . "%' AND quote_id =  " . $quote_id;







		$row  = $this->dbl->dbl_conn->rawQuery($sql);







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















	public function updateCartItemQty($quote_id, $item_id, $qty_ordered, $total_price, $tax_percent, $tax_amount)

	{















		$params = array($qty_ordered, $total_price, $tax_percent, $tax_amount, time(), $quote_id, $item_id);















		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items set qty_ordered = ?, total_price = ?, tax_percent = ?, tax_amount = ?, updated_at = ?  where quote_id= ? AND item_id = ? ", $params);















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















	public function getCouponData($coupon_code, $coupon_type = '', $customer_id = '')







	{







		$type = '';







		$customer_email_address = '';







		if (!empty($customer_id) && $customer_id != 0) {















			if ($coupon_type != "") {







				$param_one = array($coupon_code, $coupon_type);







				$query_one = "SELECT s.*, sc.coupon_id, sc.coupon_code, sc.email_address FROM salesrule as s INNER JOIN salesrule_coupon as sc ON sc.rule_id = s.rule_id   WHERE sc.coupon_code = ? AND s.coupon_type = ?   AND s.remove_flag = 0 AND s.status = 1 limit 1 ";

			} else {







				$param_one = array($coupon_code);







				$query_one = "SELECT s.*, sc.coupon_id, sc.coupon_code, sc.email_address FROM salesrule as s INNER JOIN salesrule_coupon as sc ON sc.rule_id = s.rule_id   WHERE sc.coupon_code = ? AND s.remove_flag = 0 AND s.status = 1 limit 1 ";

			}















			$coupon_row = $this->dbl->dbl_conn->rawQueryOne($query_one, $param_one);







			//var_dump($coupon_row);exit;







			if ($this->dbl->dbl_conn->getLastErrno() === 0) {







				if ($this->dbl->dbl_conn->count > 0) {















					$type = $coupon_row['type'];

				} else {







					//return false;







				}

			} else {







				//return false;







			}















			if ((isset($type) && $type == 4) && (isset($customer_id) && $customer_id > 0)) {







				$CustomerInfo = $this->getCustomerDataById($customer_id);







				$customer_email_address = $CustomerInfo['email_id'];

			}

		}















		$customer_inner_join = '';







		$where_cust_type = '';







		if (!empty($customer_id) && $customer_id != 0) {







			if (isset($type) && ($type == 4) && ($customer_email_address != '')) {







				$where_cust_type = " AND sc.email_address='$customer_email_address ' ";

			}

		} else {







			$where_cust_type = " AND FIND_IN_SET('1', s.apply_to)";

		}















		if ($coupon_type != "") {







			$param = array($coupon_code, $coupon_type, 0, 1);







			$query = "SELECT s.*, sc.coupon_id, sc.coupon_code FROM salesrule as s INNER JOIN salesrule_coupon as sc ON sc.rule_id = s.rule_id $customer_inner_join WHERE sc.coupon_code = ? AND s.coupon_type = ? $where_cust_type  AND s.remove_flag = ? AND s.status = ?";

		} else {







			$param = array($coupon_code, 0, 1);







			$query = "SELECT s.*, sc.coupon_id, sc.coupon_code FROM salesrule as s INNER JOIN salesrule_coupon as sc ON sc.rule_id = s.rule_id $customer_inner_join WHERE sc.coupon_code = ? $where_cust_type  AND s.remove_flag = ? AND s.status = ?";

		}







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





	public function getCouponData_new($coupon_code, $coupon_type = '', $customer_id = '')







	{







		$type = '';







		$customer_email_address = '';







		if (!empty($customer_id) && $customer_id != 0) {















			$param_one = array($coupon_code);







			$query_one = "SELECT s.*, sc.coupon_id, sc.coupon_code, sc.email_address FROM salesrule as s INNER JOIN salesrule_coupon as sc ON sc.rule_id = s.rule_id   WHERE sc.coupon_code = ? AND s.remove_flag = 0 AND s.status = 1 limit 1 ";















			$coupon_row = $this->dbl->dbl_conn->rawQueryOne($query_one, $param_one);







			//var_dump($coupon_row);exit;







			if ($this->dbl->dbl_conn->getLastErrno() === 0) {







				if ($this->dbl->dbl_conn->count > 0) {















					$type = $coupon_row['type'];

				} else {







					//return false;







				}

			} else {







				//return false;







			}















			if ((isset($type) && $type == 4) && (isset($customer_id) && $customer_id > 0)) {







				$CustomerInfo = $this->getCustomerDataById($customer_id);







				$customer_email_address = $CustomerInfo['email_id'];

			}

		}















		$customer_inner_join = '';







		$where_cust_type = '';







		if (!empty($customer_id) && $customer_id != 0) {







			if (isset($type) && ($type == 4) && ($customer_email_address != '')) {







				$where_cust_type = " AND sc.email_address='$customer_email_address ' ";

			}

		} else {







			$where_cust_type = " AND FIND_IN_SET('1', s.apply_to)";

		}















		$param = array($coupon_code, 0, 1);







		$query = "SELECT s.*, sc.coupon_id, sc.coupon_code FROM salesrule as s INNER JOIN salesrule_coupon as sc ON sc.rule_id = s.rule_id $customer_inner_join WHERE sc.coupon_code = ? $where_cust_type  AND s.remove_flag = ? AND s.status = ?";







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









	public function removeCouponCode($quote_id, $coupon_code, $coupon_type)

	{

		$QuoteData = $this->getQuoteDataById($quote_id);

		// $applied_rule_ids = explode(",", $QuoteData['applied_rule_ids']);

		// $applied_rule_arr = array_filter($applied_rule_ids);

		// $CouponData = $this->getCouponDataByCode($coupon_code);

		// $to_remove = array($CouponData['rule_id']);

		// $result = array_diff($applied_rule_arr, $to_remove);

		$rule_id = NULL;



		// if (!empty($result)) {

		// 	$rule_id = "," . implode(",", $result);

		// }

		if ($coupon_type == 1) {

			$voucher_code = NULL;

			$voucher_amount = 0.00;

			$params = array($rule_id, $voucher_code, $voucher_amount, $quote_id);

			$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET applied_rule_ids = ?, voucher_code = ?, voucher_amount = ? WHERE quote_id= ? ", $params);



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

		} else {

			$rule_ids = NULL;

			$couponcode = NULL;

			$params = array($rule_ids, 0.00, 0.00, 0.00, $quote_id);

			$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ? WHERE quote_id= ?", $params);



			if ($this->dbl->dbl_conn->getLastErrno() === 0) {

				$flag = true;

				if ($this->dbl->dbl_conn->count > 0) {

					$params = array($rule_id, $couponcode, 0.00, 0.00, 0.00, $quote_id);



					$updtrow = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET applied_rule_ids = ?, coupon_code = ?, base_discount_amount = ?, discount_amount = ?, discount_percent = ?  WHERE quote_id= ? ", $params);

					return $flag;

				} else {

					return false;

				}

			} else {

				return false;

			}

		}

	}





	public function removeCouponCodeNew($quote_id)

	{

		// echo $item_id;

		// die;

		$rule_id = NULL;





		$rule_ids = NULL;



		$couponcode = NULL;



		$params = array($rule_id, $couponcode, 0.00, 0.00, 0.00, $quote_id);



		$updtrow = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET applied_rule_ids = ?, coupon_code = ?, base_discount_amount = ?, discount_amount = ?, discount_percent = ?  WHERE quote_id= ? ", $params);





		$params1 = array($rule_ids, 0.00, 0.00, 0.00, $quote_id);



		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ? WHERE quote_id= ?", $params1);



		if ($this->dbl->dbl_conn->getLastErrno() === 0) {



			// $flag = true;



			if ($this->dbl->dbl_conn->count > 0) {



				return $updtrow;

			} else {



				return false;

			}

		} else {



			return false;

		}

	}











	public function UpateQuoteCouponDiscount($quote_id, $rule_ids, $coupon_code, $discount_amount, $discount_percent, $coupon_type)







	{







		$QuoteData = $this->getQuoteDataById($quote_id);







		$applied_rule_ids = $QuoteData['applied_rule_ids'];







		$rule_ids = $applied_rule_ids . $rule_ids;















		if ($coupon_type == 1) {







			$params = array($rule_ids, $coupon_code, $discount_amount, $quote_id);







			$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET applied_rule_ids = ?, voucher_code = ?, voucher_amount = ? WHERE quote_id= ? ", $params);

		} else {







			$params = array($rule_ids, $coupon_code, $discount_amount, $discount_amount, $discount_percent, $quote_id);







			$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET applied_rule_ids = ?, coupon_code = ?, base_discount_amount = ?, discount_amount = ?, discount_percent = ?  WHERE quote_id= ? ", $params);

		}







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















	public function updateCartItemsCouponDiscount($quote_id, $rule_ids, $discount_percent, $cartItems)







	{







		$disallowed_product_ids = ["4314", "4316", "4312", "4310", "4308", "4301","4302","4303","4304","4305","4306"];

		$international_product_ids =["226","4064","189","3257","3258","441","4314", "4316", "4312", "4310", "4308", "4301","4302","4303","4304","4305","4306"];



		if (is_array($cartItems) && count($cartItems) > 0) {



			$flag = true; // Initialize flag to track success

	

			

		

			foreach ($cartItems as $value) {







				$item_id = $value['item_id'];







				$qty = $value['qty_ordered'];



				// Check if the product ID is in the disallowed list

				if (in_array($value['product_id'], $disallowed_product_ids)) {

					continue; // Skip discount application for this product

				}



				if (in_array($value['product_id'], $international_product_ids)) {

					continue; // Skip discount application for this product

				}



				



				$getProduct = $this->getProduct($value['product_id']);

				$getCategory = $this->getCategory($value['parent_product_id']);

				

				if($getCategory){

					$price =  $value['price']; // written on 19-09-24

				}else{

					$price = $value['price'] - $getProduct['shipping_amount'];// written on 19-09-24

				}



				$perItemDiscount = $price * ($discount_percent / 100); // written on 19-09-24







				// $perItemDiscount = $value['price'] * ($discount_percent / 100); // commented on 19-09-24







				$total_discount_amount = $perItemDiscount * $qty;















				$params = array($rule_ids, $discount_percent, $perItemDiscount, $total_discount_amount, $item_id, $quote_id);







				$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET applied_rule_ids = ?, discount_percent = ?, discount_amount = ?, total_discount_amount = ? WHERE item_id = ? AND quote_id= ?", $params);















				if ($this->dbl->dbl_conn->getLastErrno() === 0) {







					$flag = true;

				} else {







					$flag = false;

				}

			}







			return $flag;

		} else {







			return false;

		}

	}















	function getProductCategory($product_type, $product_id, $parent_product_id = '')

	{







		if ($product_id != '') {















			if ($product_type == 'conf-simple') {







				$sql = "SELECT GROUP_CONCAT(category_ids) as category_ids FROM products_category where product_id = $parent_product_id ";

			} else {







				$sql = "SELECT GROUP_CONCAT(category_ids) as category_ids FROM products_category where product_id = $product_id ";

			}







			$row = $this->dbl->dbl_conn->rawQueryOne($sql);























			if ($this->dbl->dbl_conn->getLastErrno() === 0) {







				if ($this->dbl->dbl_conn->count > 0) {







					return $row;

				} else {







					return false;

				}

			} else {







				return false;

			}

		} else {







			return false;

		}

	}















	function getCustomerInfo($customer_id = '')

	{























		if ($customer_id != '' && $customer_id > 0) {















			$sql = "SELECT * FROM customers where id = $customer_id ";















			$row = $this->dbl->dbl_conn->rawQueryOne($sql);























			if ($this->dbl->dbl_conn->getLastErrno() === 0) {







				if ($this->dbl->dbl_conn->count > 0) {







					return $row;

				} else {







					return false;

				}

			} else {







				return false;

			}

		} else {







			return false;

		}

	}















	function getQuoteItems($quote_id)

	{























		if ($quote_id != '' && $quote_id > 0) {















			$sql = "SELECT * FROM sales_quote_items where quote_id = $quote_id ";















			$row = $this->dbl->dbl_conn->rawQuery($sql);























			if ($this->dbl->dbl_conn->getLastErrno() === 0) {







				if ($this->dbl->dbl_conn->count > 0) {







					return $row;

				} else {







					return false;

				}

			} else {







				return false;

			}

		} else {







			return false;

		}

	}































	public function getCouponUsedCountInOrders($coupon_code, $coupon_type, $customer_id = '')







	{















		if ($customer_id != '' && $customer_id > 0) {







			if ($coupon_type == 0) {







				$sql =  "SELECT count(*) used_count FROM sales_order where `coupon_code` = '$coupon_code'  AND customer_id=$customer_id AND ( (status <> 7) AND ((status <> 3) OR cancel_by_customer != 0)) ";

			} else if ($coupon_type == 1) {







				$sql =  "SELECT count(*) used_count FROM sales_order where `voucher_code` = '$coupon_code'  AND customer_id=$customer_id  AND ( (status <> 7) AND ((status <> 3) OR  cancel_by_customer != 0))";

			}

		} else {







			if ($coupon_type == 0) {







				$sql =  "SELECT count(*) used_count FROM sales_order where `coupon_code` = '$coupon_code' AND ( (status <> 7) AND ((status <> 3) OR  cancel_by_customer != 0))";

			} else if ($coupon_type == 1) {







				$sql =  "SELECT count(*) used_count FROM sales_order where `voucher_code` = '$coupon_code' AND ( (status <> 7) AND ((status <> 3) OR  cancel_by_customer != 0))";

			}

		}







		$row  = $this->dbl->dbl_conn->rawQueryOne($sql);















		if ($this->dbl->dbl_conn->getLastErrno() === 0) {







			if ($this->dbl->dbl_conn->count > 0) {







				return $row['used_count'];

			} else {







				return false;

			}

		} else {







			return false;

		}

	}























	public function getCustomerDataById($customer_id)







	{







		$sql = "SELECT * FROM customers where `id` = '$customer_id'";







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















	public function updateCartItems($quote_id, $item_id, $price, $total_price)







	{















		$params = array($price, $total_price, $quote_id, $item_id);















		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET price = ?, total_price = ? WHERE quote_id= ? AND item_id = ? ", $params);















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























	public function getExeptionalTaxesData()

	{















		$sql = "SELECT * FROM  `exceptional_taxes_set`";







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















	public function getExeptionalCategories()

	{























		$sql = "SELECT * FROM  `exceptional_taxes_set_details` where exc_taxes_id = 1";







		$row  = $this->dbl->dbl_conn->rawQuery($sql);







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























	function getCategoriesForExceptional($product_type, $product_id, $parent_product_id = '')

	{















		if ($product_id != '') {















			if ($product_type == 'conf-simple') {







				$sql = "SELECT GROUP_CONCAT(category_ids) as category_ids FROM products_category where product_id = $parent_product_id";

			} else {







				$sql = "SELECT GROUP_CONCAT(category_ids) as category_ids FROM products_category where product_id = $product_id";

			}







			$row = $this->dbl->dbl_conn->rawQueryOne($sql);























			if ($this->dbl->dbl_conn->getLastErrno() === 0) {







				if ($this->dbl->dbl_conn->count > 0) {







					return $row;

				} else {







					return false;

				}

			} else {







				return false;

			}

		} else {







			return false;

		}

	}















	function updateTaxPercentQuote($quote_id, $quote_item_id, $tax_percent, $tax_amount)

	{































		$params = array($tax_percent, $tax_amount, $quote_id, $quote_item_id);







		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote_items SET tax_percent = ?, tax_amount = ? WHERE quote_id= ? AND item_id = ? ", $params);















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















	function updateQuoteCurrency($QuoteId, $currency_name, $currency_code_session, $currency_conversion_rate, $default_currency_flag, $currency_symbol)

	{







		if ($currency_name === '') {







			// website not using currency







			return true;

		}















		$params = array($currency_name, $currency_code_session, $currency_conversion_rate, $currency_symbol, $default_currency_flag, $QuoteId);







		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_quote SET currency_name = ?, currency_code_session = ?, currency_conversion_rate = ?, currency_symbol = ?, default_currency_flag = ? WHERE quote_id= ?", $params);















		if ($this->dbl->dbl_conn->getLastErrno() === 0) {







			return $this->dbl->dbl_conn->count > 0;

		}















		return false;

	}















	public function getTotalDefaultQtyofBundle($QuoteId, $product_bundle_ids, $product_id, $parent_product_id)

	{















		$parent_product_id = ($parent_product_id != '') ? $parent_product_id : 0;















		$sql = "SELECT SUM(products_bundles.default_qty*sales_quote_items.qty_ordered) as total_qty FROM products_bundles Join sales_quote_items ON sales_quote_items.product_id = products_bundles.bundle_product_id where IF ((products_bundles.product_type = 'configurable'), products_bundles.product_id = " . $parent_product_id . ",products_bundles.product_id = " . $product_id . " AND products_bundles.product_parent_id = " . $parent_product_id . ") AND products_bundles.bundle_product_id IN (" . $product_bundle_ids . ") AND sales_quote_items.quote_id =" . $QuoteId;







		$row  = $this->dbl->dbl_conn->rawQueryOne($sql);















		if ($this->dbl->dbl_conn->getLastErrno() === 0) {







			if ($this->dbl->dbl_conn->count > 0) {







				return $row['total_qty'];

			} else {







				return false;

			}

		} else {







			return false;

		}

	}

}

