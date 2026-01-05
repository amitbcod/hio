<?php
Class DbOrders{
	private $dbl;

	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}

	 public function getOrdersData($customer_id,$limit,$offset)
	 {

		$offset = (isset($offset) && $offset != Null &&  $offset != '') ? $offset :  0;

		$sql =  "SELECT `order`.`order_id`,`order`.`increment_id`,`order`.`created_at`,`order`.`shipping_amount`,`order`.`shipping_charge`,`order`.`voucher_amount`,`order`.`discount_amount`,`order`.`base_subtotal`,`order`.`tax_amount`,`order`.`subtotal`,`order`.`shipping_tax_amount`,`order`.`shipping_tax_percent`,`order`.`grand_total`,`order`.`status`,`order`.`tracking_complete_date`,`order`.`payment_final_charge`,`order`.`payment_charge`,`order`.`invoice_id`,`inv`.`invoice_file`,`order`.`invoice_self` FROM `sales_order` as `order` LEFT JOIN `invoicing` as `inv` ON `order`.`invoice_id` = `inv`.`id`  where `order`.`customer_id` = '$customer_id' AND `order`.`status` != 7 order by `order`.`created_at` DESC LIMIT $limit OFFSET $offset ";
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

	 public function countgetOrdersData($customer_id)
	 {

		$sql =  "SELECT order_id,increment_id,created_at,shipping_amount,shipping_charge,voucher_amount,discount_amount,base_subtotal,tax_amount,subtotal,shipping_tax_amount,shipping_tax_percent,grand_total,status,tracking_complete_date FROM sales_order where `customer_id` = '$customer_id' AND status!=7 order by created_at DESC ";
		$row  = $this->dbl->dbl_conn->rawQuery($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $this->dbl->dbl_conn->count;
			}else{
				return false;
			}
		}else{
			return false;
		}

	 }


	 public function getReturnOrdersData($shopcode,$shopid,$order_id)
	 {
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.sales_order_return where `order_id` = '$order_id' AND status NOT IN (0,1) order by created_at DESC";
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

	 public function getReturnOrdersDataMultiple($order_ids)
	 {
		$order_ids_string = implode(',',$order_ids);
		$sql =  "SELECT * FROM sales_order_return where `order_id` IN ($order_ids_string) AND status NOT IN (0,1) order by created_at DESC";
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

	 public function getReturnOrdersItemsData($shopcode,$shopid,$order_id,$return_order_id)
	 {
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable
		$sql =  "SELECT * FROM $shop_db.sales_order_return_items where `order_id` = '$order_id'  AND return_order_id= $return_order_id ";
		$row  = $this->dbl->dbl_conn->rawQuery($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				$return_order_items=array();
				foreach($row as $ro_item){
					$order_item_id=$ro_item['order_item_id'];
					$SingleRow=$this->getSingleOrderItemDetail($shopcode,$order_item_id);
					if($SingleRow !=false){
						if($SingleRow['product_type'] == 'conf-simple'){
							$ro_item['product_variants']= $SingleRow['product_variants'];
							$ro_item['actual_qty_order']= $SingleRow['qty_ordered'];
							$ro_item['product_name']= $SingleRow['product_name'];
							$ro_item['product_type']= $SingleRow['product_type'];
							$ro_item['product_inv_type']= $SingleRow['product_inv_type'];
							$parent_id = $SingleRow['parent_product_id'];
							$product_id = $SingleRow['product_id'];

							$param2 = array($parent_id);
							$query2 = "SELECT media_variant_id, base_image, estimate_delivery_time, url_key FROM $shop_db.products WHERE id = ?";
							$productData = $this->dbl->dbl_conn->rawQueryOne($query2,$param2);

							if($productData['media_variant_id'] > 0){
								$param3 = array($parent_id,1,$parent_id,$product_id,$productData['media_variant_id']);
								$query3 = "SELECT image FROM $shop_db.products_media_gallery WHERE product_id = ? AND is_default_variant = ? AND attr_option_id = (SELECT attr_value from $shop_db.products_variants where parent_id = ? and product_id = ? and attr_id = ? )";
								$productMedia = $this->dbl->dbl_conn->rawQueryOne($query3,$param3);
							}else{
								$param3 = array($parent_id,$product_id);
								$query3 = "SELECT image FROM $shop_db.products_media_gallery WHERE product_id = ? AND child_id = ? ORDER BY id LIMIT 1";
								$productMedia = $this->dbl->dbl_conn->rawQueryOne($query3,$param3);
							}

							if ($this->dbl->dbl_conn->getLastErrno() === 0){
								if ($this->dbl->dbl_conn->count > 0){
									$ro_item['base_image']= $productMedia['image'];
								}else{
									$ro_item['base_image']= $productData['base_image'];
								}
							} else {
								$ro_item['base_image']= $productData['base_image'];
							}




							$ro_item['estimate_delivery_time']= $productData['estimate_delivery_time'];
							$ro_item['url_key']= $productData['url_key'];

						}else{
							$ro_item['actual_qty_order']= $SingleRow['qty_ordered'];
							$ro_item['product_name']= $SingleRow['product_name'];
							$ro_item['product_type']= $SingleRow['product_type'];
							$ro_item['product_inv_type']= $SingleRow['product_inv_type'];
							$product_id = $SingleRow['product_id'];
							$param2 = array($product_id);
							$query2 = "SELECT base_image, estimate_delivery_time, url_key FROM $shop_db.products WHERE id = ?";
							$productData = $this->dbl->dbl_conn->rawQueryOne($query2,$param2);
							//print_r($getEstimateTime);
							$ro_item['base_image']= $productData['base_image'];
							$ro_item['estimate_delivery_time']= $productData['estimate_delivery_time'];
							$ro_item['url_key']= $productData['url_key'];

						}

						$return_order_items[] = $ro_item;
					}
				}

				return $return_order_items;
			}else{
				return false;
			}
		}else{
			return false;
		}

	 }

	 public function getReturnOrdersItemsDataMulti($order_ids,$return_order_ids)
	 {
		$order_ids_string = implode(',',$order_ids);
		$return_order_ids_string = implode(',',$return_order_ids);
		$sql =  "SELECT * FROM sales_order_return_items where `order_id` IN ($order_ids_string)  AND return_order_id IN ($return_order_ids_string) ";
		$row  = $this->dbl->dbl_conn->rawQuery($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				$return_order_items=array();
				$item_order_ids = array_column($row, 'order_id');
				$OrderItemsMultidata=$this->getOrderItemsMultiidsReturnOrder($item_order_ids);
				$this->order_item_data = array_group($OrderItemsMultidata, 'item_id');
				//getOrderItemsMultiItemIds
				foreach($row as $ro_item){
					$order_item_id=$ro_item['order_item_id'];
					$SingleRow=$this->order_item_data[$order_item_id][0] ?? false;
					if($SingleRow !=false){
						if($SingleRow['product_type'] == 'conf-simple'){
							$ro_item['product_variants']= $SingleRow['product_variants'];
							$ro_item['actual_qty_order']= $SingleRow['qty_ordered'];
							$ro_item['product_name']= $SingleRow['product_name'];
							$ro_item['product_type']= $SingleRow['product_type'];
							$ro_item['product_inv_type']= $SingleRow['product_inv_type'];
							$parent_id = $SingleRow['parent_product_id'];
							$product_id = $SingleRow['product_id'];

							$param2 = array($parent_id);
							$query2 = "SELECT media_variant_id, base_image, estimate_delivery_time, url_key FROM products WHERE id = ?";
							$productData = $this->dbl->dbl_conn->rawQueryOne($query2,$param2);

							if($productData['media_variant_id'] > 0){
								$param3 = array($parent_id,1,$parent_id,$product_id,$productData['media_variant_id']);
								$query3 = "SELECT image FROM products_media_gallery WHERE product_id = ? AND is_default_variant = ? AND attr_option_id = (SELECT attr_value from products_variants where parent_id = ? and product_id = ? and attr_id = ? )";
								$productMedia = $this->dbl->dbl_conn->rawQueryOne($query3,$param3);
							}else{
								$param3 = array($parent_id,$product_id);
								$query3 = "SELECT image FROM products_media_gallery WHERE product_id = ? AND child_id = ? ORDER BY id LIMIT 1";
								$productMedia = $this->dbl->dbl_conn->rawQueryOne($query3,$param3);
							}

							if ($this->dbl->dbl_conn->getLastErrno() === 0){
								if ($this->dbl->dbl_conn->count > 0){
									$ro_item['base_image']= $productMedia['image'];
								}else{
									$ro_item['base_image']= $productData['base_image'];
								}
							} else {
								$ro_item['base_image']= $productData['base_image'];
							}




							$ro_item['estimate_delivery_time']= $productData['estimate_delivery_time'];
							$ro_item['url_key']= $productData['url_key'];

						}else{
							$ro_item['actual_qty_order']= $SingleRow['qty_ordered'];
							$ro_item['product_name']= $SingleRow['product_name'];
							$ro_item['product_type']= $SingleRow['product_type'];
							$ro_item['product_inv_type']= $SingleRow['product_inv_type'];
							$product_id = $SingleRow['product_id'];
							$param2 = array($product_id);
							$query2 = "SELECT base_image, estimate_delivery_time, url_key FROM products WHERE id = ?";
							$productData = $this->dbl->dbl_conn->rawQueryOne($query2,$param2);
							$ro_item['base_image']= $productData['base_image'];
							$ro_item['estimate_delivery_time']= $productData['estimate_delivery_time'];
							$ro_item['url_key']= $productData['url_key'];

						}

						$return_order_items[] = $ro_item;
					}
				}
				return $return_order_items;
			}else{
				return false;
			}
		}else{
			return false;
		}

	 }

	 public function getReturnOrderDataById($shopcode,$shopid,$return_order_id)
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.sales_order_return where `return_order_id` = '$return_order_id' ";
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



	 public function getOrderDataById($order_id)
	 {
		 $sql =  "SELECT `order`.*,`inv`.`invoice_file` FROM `sales_order` as `order` LEFT JOIN `invoicing` as `inv` ON `order`.`invoice_id` = `inv`.`id` where `order`.`order_id` = '$order_id' ";
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

	 public function get_single_Orderid($increment_id)
	{
		
		
		// $shop_db =  DB_NAME_SHOP_PRE; // constant variable
		// $main_db = DB_NAME; //Constant variable

	 $sql =  "SELECT `order_id` FROM `sales_order` where `increment_id` = '$increment_id' ";
	
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

	public function get_single_OrderData($order_id)
	{
		
		
		// $shop_db =  DB_NAME_SHOP_PRE; // constant variable
		// $main_db = DB_NAME; //Constant variable

	 $sql =  "SELECT `order_id`,`is_split` FROM `sales_order` where `order_id` = '$order_id' ";
	
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

	public function get_single_Order_items_Data($order_id)
	{
		// $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		// $main_db = DB_NAME; //Constant variable

		$sql =  "SELECT `publisher_id` FROM `sales_order_items` where `order_id` IN ($order_id)  group by `publisher_id`";
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

	public function get_order_id_for_split($order_id)
	{
		// $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		// $main_db = DB_NAME; //Constant variable

		$sql =  "SELECT GROUP_CONCAT(DISTINCT `order_id`) as `order_ids` FROM sales_order where `order_id` = $order_id OR `main_parent_id` = $order_id";
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

	public function get_tracking_data($order_id)
	{
		// $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		// $main_db = DB_NAME; //Constant variable

		$sql =  "SELECT * FROM `sales_order_shipment_details` where `order_id` IN ($order_id)";
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

	}

	//creat by rahul
	//start
	public function get_order_full_data($order_id)
	{
		// $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		// $main_db = DB_NAME; //Constant variable

		$sql = "SELECT `increment_id`,`customer_firstname`,`customer_lastname`,`base_grand_total`,`status`,`order_id` FROM sales_order where `order_id` = '$order_id'";
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

	public function order_product_details($order_id)
	{
		// $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		// $main_db = DB_NAME; //Constant variable

		$sql = "SELECT `product_id`,`product_name`,`qty_ordered`,`cover_price`,`total_price`,`total_discount_amount`,`gift_name` FROM sales_order_items where `order_id` = '$order_id'";
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

	}

	//end

	public function get_order_increment_id($order_id)
	{
		// $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		// $main_db = DB_NAME; //Constant variable

		$sql = "SELECT `increment_id` FROM sales_order where `order_id` = '$order_id'";
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

	public function get_order_increment_id_b2b($shopcode,$order_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$sql =  "SELECT `increment_id` FROM $shop_db.b2b_orders where `order_id` = '$order_id'";
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

	public function get_order_id_b2b($shopcode,$order_id,$own_shop_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$sql =  "SELECT GROUP_CONCAT(DISTINCT `order_id`) as `order_ids` FROM $shop_db.b2b_orders where `webshop_order_id` IN ($order_id) AND `shop_id` = '$own_shop_id' ";
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

	public function get_tracking_data_for_b2b($shopcode,$order_ids)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$sql =  "SELECT * FROM $shop_db.`b2b_order_shipment_details` WHERE `order_id` in ($order_ids)";
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




	 public function getOrderItems($order_id)
	 {

		 $sql =  "SELECT * FROM sales_order_items where `order_id` = '$order_id' AND `product_type`!= 'bundle' order by item_id ASC";
		 $Result  = $this->dbl->dbl_conn->rawQuery($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				$OrderItems=array();

				foreach($Result as $value){
							//print_r ($value);
							//echo $value['product_id'];
							if($value['product_type'] == 'conf-simple'){
								$parent_id = $value['parent_product_id'];
								$product_id = $value['product_id'];
								$param2 = array($parent_id);
								$query2 = "SELECT media_variant_id, base_image, estimate_delivery_time, url_key FROM products WHERE id = ?";
								$productData = $this->dbl->dbl_conn->rawQueryOne($query2,$param2);

								if($productData['media_variant_id'] > 0){
									$param3 = array($parent_id,1,$parent_id,$product_id,$productData['media_variant_id']);
									$query3 = "SELECT image FROM products_media_gallery WHERE product_id = ? AND is_default_variant = ? AND attr_option_id = (SELECT attr_value from products_variants where parent_id = ? and product_id = ? and attr_id = ? )";
									$productMedia = $this->dbl->dbl_conn->rawQueryOne($query3,$param3);
								}else{
									$param3 = array($parent_id,$product_id);
									$query3 = "SELECT image FROM products_media_gallery WHERE product_id = ? AND child_id = ? ORDER BY id LIMIT 1";
									$productMedia = $this->dbl->dbl_conn->rawQueryOne($query3,$param3);
								}

								if ($this->dbl->dbl_conn->getLastErrno() === 0){
									if ($this->dbl->dbl_conn->count > 0){
										$value['base_image']= $productMedia['image'];
									}else{
										$value['base_image']= $productData['base_image'];
									}
								} else {
									$value['base_image']= $productData['base_image'];
								}


								$value['estimate_delivery_time']= $productData['estimate_delivery_time'];
								$value['url_key']= $productData['url_key'];

							}else{
								$product_id = $value['product_id'];
								$param2 = array($product_id);
								$query2 = "SELECT base_image, estimate_delivery_time, url_key FROM products WHERE id = ?";
								$productData = $this->dbl->dbl_conn->rawQueryOne($query2,$param2);


								//print_r($getEstimateTime);
								$value['base_image']= $productData['base_image'];
								$value['estimate_delivery_time']= $productData['estimate_delivery_time'];
								$value['url_key']= $productData['url_key'];

							}

							$IsReturnedItem=$this->getReturnQtyByOrderId($order_id,$value['item_id']);
							if($IsReturnedItem==0){
								$value['item_display_status']=1;   // display
							}else{
								if($value['qty_ordered']>$IsReturnedItem)
								{
									$value['item_display_status']=1;   // display
									$value['qty_return']=$IsReturnedItem;  // for reference
									$value['qty_ordered']=$value['qty_ordered']-$IsReturnedItem;   // update qty_order as per rule
								}else{
									$value['item_display_status']=2;   // skip  for item display
									$value['qty_return']=$IsReturnedItem;  // for reference
								}
							}

							$OrderItems[] = $value;
						}


				return $OrderItems;

			}else{
				return false;
			}
		}else{
			return false;
		}

	 }

	 public function getOrderItemsMultiids($order_ids)
	 {

		// $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		// $main_db = DB_NAME; //Constant variable
		$order_ids_string = implode(',',$order_ids);
		$sql =  "SELECT item_id,order_id,product_id,parent_product_id,parent_item_id,product_type,product_name,product_code,product_variants,estimate_delivery_time,qty_ordered,qty_scanned,price,total_price,tax_amount,discount_amount,total_discount_amount,can_be_returned,sub_start_date,sub_end_date FROM sales_order_items where `order_id` IN ($order_ids_string) AND `product_type`!= 'bundle' order by item_id ASC";
		$Result  = $this->dbl->dbl_conn->rawQuery($sql);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				$OrderItems=array();
				$product_ids = array_column($Result, 'product_id');
				$parent_product_ids = array_column($Result, 'parent_product_id');
				$item_ids = array_column($Result, 'item_id');
				$item_ids_string = implode(',',$item_ids);
				// multi product data
				$product_ids_string = implode(',',$product_ids);
				$parent_product_ids_string = implode(',',$parent_product_ids);
				$sql2 = "SELECT id,media_variant_id, base_image, estimate_delivery_time, url_key FROM products WHERE `id` IN ($product_ids_string) OR `id` IN ($parent_product_ids_string)";
				$Result_product_id  = $this->dbl->dbl_conn->rawQuery($sql2);
				$item_products = array_group($Result_product_id, 'id');

				// end multi product data
				//start
				$sqlb2b =  "SELECT SUM(oi.qty_return) as qty_return, oi.order_id,oi.order_item_id FROM sales_order_return_items oi LEFT JOIN sales_order_return  as sor ON oi.return_order_id = sor.return_order_id where  oi.order_id IN ($order_ids_string) AND oi.order_item_id IN ($item_ids_string) AND sor.status NOT IN (0,1)";
				$rowb2b  = $this->dbl->dbl_conn->rawQuery($sqlb2b);
				$item_products_return = array_group($rowb2b, 'order_item_id');
				//end

				// print_R($item_products);
				foreach($Result as $value){
						//addition added
							if($value['product_type'] == 'conf-simple'){
								$parent_id = $value['parent_product_id'];
								$product_id = $value['product_id'];
								$productData=(isset($item_products[$parent_id]) && isset($item_products[$parent_id][0]) ? $item_products[$parent_id][0]  : '' );
								if(isset($productData['media_variant_id']) &&  $productData['media_variant_id'] > 0){
									$param3 = array($parent_id,1,$parent_id,$product_id,$productData['media_variant_id']);
									$query3 = "SELECT image FROM products_media_gallery WHERE product_id = ? AND is_default_variant = ? AND attr_option_id = (SELECT attr_value from products_variants where parent_id = ? and product_id = ? and attr_id = ? )";
									$productMedia = $this->dbl->dbl_conn->rawQueryOne($query3,$param3);
								}else{
									$param3 = array($parent_id,$product_id);
									// print_R($param3);
									$query3 = "SELECT image FROM products_media_gallery WHERE product_id = ? AND child_id = ? ORDER BY id LIMIT 1";
									$productMedia = $this->dbl->dbl_conn->rawQueryOne($query3,$param3);
								}
								
								if ($this->dbl->dbl_conn->getLastErrno() === 0){
									
									if ($this->dbl->dbl_conn->count > 0){
										
										$value['base_image']= $productMedia['image'];
									}else{
										$value['base_image']= (isset($productData['base_image']) ? $productData['base_image'] :'' );
									}
								} else {
									$value['base_image']= (isset($productData['base_image']) ? : '');
								}


								$value['estimate_delivery_time']= (isset($productData['estimate_delivery_time'])? $productData['estimate_delivery_time'] : '');
								$value['url_key']= (isset($productData['url_key']) ? $productData['url_key'] : '');

							}else{
								$product_id = $value['product_id'];
								$productData=$item_products[$product_id][0];
								$value['base_image']= $productData['base_image'];
								$value['estimate_delivery_time']= $productData['estimate_delivery_time'];
								$value['url_key']= $productData['url_key'];

							}

							$IsReturnedItem=0;
							if(isset($item_products_return[$value['item_id']])){
								if(isset($item_products_return[$value['item_id']][0]) && isset($item_products_return[$value['item_id']][0][$value['order_id']])){
									$IsReturnedItem=$item_products_return[$value['item_id']][0]['qty_return'];
								}
							}

							if($IsReturnedItem==0){
								$value['item_display_status']=1;   // display
							}else{
								if($value['qty_ordered']>$IsReturnedItem)
								{
									$value['item_display_status']=1;   // display
									$value['qty_return']=$IsReturnedItem;  // for reference
									$value['qty_ordered']=$value['qty_ordered']-$IsReturnedItem;   // update qty_order as per rule
								}else{
									$value['item_display_status']=2;   // skip  for item display
									$value['qty_return']=$IsReturnedItem;  // for reference
								}
							}

							$OrderItems[] = $value;
						}
						// print_R($OrderItems);die();
				return $OrderItems;

			}else{
				return false;
			}
		}else{
			return false;
		}

	 }

	// new
	public function getOrderItemsMultiidsReturnOrder($shopcode,$shopid, $order_ids)
	{

	   $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
	   $main_db = DB_NAME; //Constant variable
	   $order_ids_string = implode(',',$order_ids);

		$sql =  "SELECT * FROM $shop_db.sales_order_items where `order_id` IN ($order_ids_string) AND `product_type`!= 'bundle' order by item_id ASC";
		$Result  = $this->dbl->dbl_conn->rawQuery($sql);

	   if ($this->dbl->dbl_conn->getLastErrno() === 0){
		   if ($this->dbl->dbl_conn->count > 0){
			   $OrderItems=array();
			   $product_ids = array_column($Result, 'product_id');
			   $parent_product_ids = array_column($Result, 'parent_product_id');
			   $item_ids = array_column($Result, 'item_id');
			   $item_ids_string = implode(',',$item_ids);
			   // multi product data
			   $product_ids_string = implode(',',$product_ids);
			   $parent_product_ids_string = implode(',',$parent_product_ids);
			   $sql2 = "SELECT id,media_variant_id, base_image, estimate_delivery_time, url_key FROM $shop_db.products WHERE `id` IN ($product_ids_string) OR `id` IN ($parent_product_ids_string)";
			   $Result_product_id  = $this->dbl->dbl_conn->rawQuery($sql2);
			   $item_products = array_group($Result_product_id, 'id');
			   //start
			   $sqlb2b =  "SELECT SUM(oi.qty_return) as qty_return, oi.order_id,oi.order_item_id FROM $shop_db.sales_order_return_items oi LEFT JOIN $shop_db.sales_order_return  as sor ON oi.return_order_id = sor.return_order_id where  oi.order_id IN ($order_ids_string) AND oi.order_item_id IN ($item_ids_string) AND sor.status NOT IN (0,1)";
			   $rowb2b  = $this->dbl->dbl_conn->rawQuery($sqlb2b);
			   $item_products_return = array_group($rowb2b, 'order_item_id');

			   foreach($Result as $value){
						   if($value['product_type'] == 'conf-simple'){
							   $parent_id = $value['parent_product_id'];
							   $product_id = $value['product_id'];
							   $productData=$item_products[$parent_id][0];
							   if($productData['media_variant_id'] > 0){
								   $param3 = array($parent_id,1,$parent_id,$product_id,$productData['media_variant_id']);
								   $query3 = "SELECT image FROM $shop_db.products_media_gallery WHERE product_id = ? AND is_default_variant = ? AND attr_option_id = (SELECT attr_value from $shop_db.products_variants where parent_id = ? and product_id = ? and attr_id = ? )";
								   $productMedia = $this->dbl->dbl_conn->rawQueryOne($query3,$param3);
							   }else{
								   $param3 = array($parent_id,$product_id);
								   $query3 = "SELECT image FROM $shop_db.products_media_gallery WHERE product_id = ? AND child_id = ? ORDER BY id LIMIT 1";
								   $productMedia = $this->dbl->dbl_conn->rawQueryOne($query3,$param3);
							   }
							   if ($this->dbl->dbl_conn->getLastErrno() === 0){
								   if ($this->dbl->dbl_conn->count > 0){
									   $value['base_image']= $productMedia['image'];
								   }else{
									   $value['base_image']= $productData['base_image'];
								   }
							   } else {
								   $value['base_image']= $productData['base_image'];
							   }

							   $value['estimate_delivery_time']= $productData['estimate_delivery_time'];
							   $value['url_key']= $productData['url_key'];

						   }else{
							   $product_id = $value['product_id'];
							   $productData=$item_products[$product_id][0];
							   $value['base_image']= $productData['base_image'];
							   $value['estimate_delivery_time']= $productData['estimate_delivery_time'];
							   $value['url_key']= $productData['url_key'];
						   }

						  	$IsReturnedItem=0;
						   if(isset($item_products_return[$value['item_id']])){
							   if(isset($item_products_return[$value['item_id']][0]) && isset($item_products_return[$value['item_id']][0][$value['order_id']])){
								   $IsReturnedItem=$item_products_return[$value['item_id']][0]['qty_return'];
							   }
						   }

						   if($IsReturnedItem==0){
							   $value['item_display_status']=1;   // display
						   }else{
							   if($value['qty_ordered']>$IsReturnedItem)
							   {
								   $value['item_display_status']=1;   // display
								   $value['qty_return']=$IsReturnedItem;  // for reference
								   $value['qty_ordered']=$value['qty_ordered']-$IsReturnedItem;   // update qty_order as per rule
							   }else{
								   $value['item_display_status']=2;   // skip  for item display
								   $value['qty_return']=$IsReturnedItem;  // for reference
							   }
						   }

						   $OrderItems[] = $value;
					   }

			   return $OrderItems;

		   }else{
			   return false;
		   }
	   }else{
		   return false;
	   }

	}
	public function getOrderProductMultiids($shopcode, $order_ids) // check used or not
	{

	   $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
	   $main_db = DB_NAME; //Constant variable
	   $order_ids_string = implode(',',$order_ids);

		$sql =  "SELECT item_id,shop_id,order_id,product_id,parent_product_id,parent_item_id,product_type,product_name,product_code,product_variants,estimate_delivery_time,qty_ordered,qty_scanned,price,total_price,tax_amount,discount_amount,total_discount_amount,can_be_returned FROM $shop_db.products where `order_id` IN ($order_ids_string) AND `product_type`!= 'bundle' order by item_id ASC";
		$Result  = $this->dbl->dbl_conn->rawQuery($sql);
	}

	 // end

	  public function getFormattedOrderAddressById($shopcode,$order_id,$address_type)
	 {
		 $address='';
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		 $main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.sales_order_address where `order_id` = $order_id AND address_type = $address_type ";
		 $row_address  = $this->dbl->dbl_conn->rawQueryOne($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				$address='<span>'.$row_address['first_name'].' '.$row_address['last_name'];
				if ($row_address['company_name']!='') {
					$address.='<br>'.$row_address['company_name'];
				}
				
				$address.='<br>'.$row_address['address_line1'].' '.$row_address['address_line2'].'<br>'.$row_address['city'].' '.$row_address['state'].'<br>'.$row_address['country'].'-'.$row_address['pincode'];
				
				if ($row_address['mobile_no']!='') {
					$address.='<br>'.$row_address['mobile_no'];
				}

				
				
				$address.='</span>';
				return $address;
			}else{
				return $address;
			}
		}else{
			return $address;
		}

	 }

	  public function getOrderPaymentDataById($order_id)
	 {

		 $sql =  "SELECT * FROM sales_order_payment where `order_id` = '$order_id'";
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


	function getFbcUserIdByShopId(){

		 $sql =  "SELECT * FROM adminusers where parent_id = 0";
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

	function getProductsDropShipAndVirtualWithQtyZero($shopcode,$shopid,$order_id,$seller_shop_id){


		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($order_id,$seller_shop_id);
		$query = "SELECT oi.item_id,oi.price,oi.qty_ordered,oi.total_price,p.shop_id,p.shop_product_id FROM $shop_db.sales_order_items as oi  INNER JOIN $shop_db.products as p ON oi.product_id = p.id LEFT JOIN $shop_db.products_inventory as pi ON oi.product_id = pi.product_id WHERE p.product_inv_type IN ('dropship','virtual') AND pi.qty <=0 and oi.order_id = ? AND p.shop_product_id > 0 AND p.shop_id= ? ";

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

	function getShopsForBTwoBOrders($order_id){

  		$param = array($order_id);
		$query = "SELECT p.shop_id FROM $shop_db.sales_order_items as oi  INNER JOIN $shop_db.products as p ON oi.product_id = p.id LEFT JOIN $shop_db.products_inventory as pi ON oi.product_id = pi.product_id WHERE p.product_inv_type IN ('dropship','virtual') AND pi.qty <=0 and oi.order_id = ? AND p.shop_product_id > 0 group by p.shop_id";

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

	function getB2BOrderItemsByIds($shopcode,$shopid,$order_id,$item_ids){


		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($order_id,$item_ids);
		$query = "SELECT * FROM $shop_db.sales_order_items where order_id=$order_id AND item_id IN ($item_ids)";

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

	function get_fbc_user_shop_details(){

		 $sql =  "SELECT * FROM shop_details ";
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



	function decrementAvailableQty($shopcode,$product_id,$qty_ordered){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$params=array($qty_ordered,$qty_ordered,$product_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.products_inventory SET available_qty = CASE   WHEN available_qty <= 0 THEN 0 WHEN available_qty >= ? THEN  available_qty - ? END WHERE product_id = ?  ",$params);

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

		/*public function getB2BOrdersForWebshop($order_id)
	 {



		 $sql =  "SELECT order_id,increment_id,created_at,grand_total,status,is_split FROM b2b_orders where `webshop_order_id` = '$order_id'  AND status!=7 order by created_at DESC";
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

	 }*/

	 public function getB2BOrdersForWebshop($webshop_order_id)
{
    // Use parameter binding to prevent SQL injection
    $sql = "SELECT 
                order_id,
                increment_id,
                created_at,
                grand_total,
                status,
                is_split
            FROM b2b_orders
            WHERE webshop_order_id = ?
              AND status != 7
            ORDER BY created_at DESC";

    $rows = $this->dbl->dbl_conn->rawQuery($sql, [$webshop_order_id]);

    if ($this->dbl->dbl_conn->getLastErrno() === 0) {
        return !empty($rows) ? $rows : false;
    }

    return false;
}


	 public function getSingleOrderItemDetail($order_item_id)
	 {
		//$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		//$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM sales_order_items where `item_id` = '$order_item_id' ";
		 $Result  = $this->dbl->dbl_conn->rawQueryOne($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				$OrderItemData=array();
				if($Result['product_type'] == 'conf-simple'){
					$parent_id = $Result['parent_product_id'];
					$product_id = $Result['product_id'];
					$param2 = array($parent_id);
					$query2 = "SELECT media_variant_id, base_image, estimate_delivery_time, url_key FROM products WHERE id = ?";
					$productData = $this->dbl->dbl_conn->rawQueryOne($query2,$param2);

					if($productData['media_variant_id'] > 0){
						$param3 = array($parent_id,1,$parent_id,$product_id,$productData['media_variant_id']);
						$query3 = "SELECT image FROM products_media_gallery WHERE product_id = ? AND is_default_variant = ? AND attr_option_id = (SELECT attr_value from products_variants where parent_id = ? and product_id = ? and attr_id = ? )";
						$productMedia = $this->dbl->dbl_conn->rawQueryOne($query3,$param3);
					}else{
						$param3 = array($parent_id,$product_id);
						$query3 = "SELECT image FROM products_media_gallery WHERE product_id = ? AND child_id = ? ORDER BY id LIMIT 1";
						$productMedia = $this->dbl->dbl_conn->rawQueryOne($query3,$param3);
					}

					if ($this->dbl->dbl_conn->getLastErrno() === 0){
						if ($this->dbl->dbl_conn->count > 0){
							$OrderItemData['base_image']= $productMedia['image'];

						}else{
							$OrderItemData['base_image']= $productData['base_image'];
						}
					} else {
						$OrderItemData['base_image']= $productData['base_image'];
					}


					$OrderItemData['estimate_delivery_time']= $productData['estimate_delivery_time'];
					$OrderItemData['url_key']= $productData['url_key'];

				}else{
					$product_id = $Result['product_id'];
					$param2 = array($product_id);
					$query2 = "SELECT base_image, estimate_delivery_time, url_key FROM products WHERE id = ?";
					$productData = $this->dbl->dbl_conn->rawQueryOne($query2,$param2);


					//print_r($getEstimateTime);
					$OrderItemData['base_image']= $productData['base_image'];
					$OrderItemData['estimate_delivery_time']= $productData['estimate_delivery_time'];
					$OrderItemData['url_key']= $productData['url_key'];

				}

				$OrderItemData = $Result;


				return $OrderItemData;

			}else{
				return false;
			}
		}else{
			return false;
		}

	 }


	 function generate_return_order_transaction_id($order_id,$order_inc_id)
     {
		$payment_id='';
        $count = $this->get_total_return_orders_by_order($order_id);

		if($count==0)
		{
			$payment_id        = 'RET-'.$order_inc_id.'-1';

		}else{
			$count         = $count+1;
			$payment_id        = 'RET-'.$order_inc_id.'-'.$count;
		}

        return $payment_id;
     }

	 public function get_total_return_orders_by_order($order_id)
	 {
		 //$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		//$main_db = DB_NAME; //Constant variable
		$total_count=0;

		 $sql =  "SELECT count(*) as total_count FROM sales_order_return where order_id=$order_id AND status NOT IN (0,1)  ";
		 $row  = $this->dbl->dbl_conn->rawQueryOne($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				$total_count =$row['total_count'];
				return $total_count;
			}else{
				return false;
			}
		}else{
			return false;
		}

	 }

	 function getReturnQtyByOrderId($order_id,$order_item_id){

		$qty_return=0;

		 //$sql =  "SELECT SUM(oi.qty_return) as qty_return FROM $shop_db.sales_order_return_items oi LEFT JOIN $shop_db.sales_order_return  as sor ON oi.order_id = sor.order_id where  oi.order_id=$order_id  AND oi.order_item_id = $order_item_id AND sor.status NOT IN (0,1)";

		 $sql =  "SELECT SUM(oi.qty_return) as qty_return FROM sales_order_return_items oi LEFT JOIN sales_order_return  as sor ON oi.return_order_id = sor.return_order_id where  oi.order_id=$order_id  AND oi.order_item_id = $order_item_id AND sor.status NOT IN (0,1)";

		 $row  = $this->dbl->dbl_conn->rawQueryOne($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $row['qty_return'];
			}else{
				return $qty_return;
			}
		}else{
			return $qty_return;
		}
	}


	 function getReturnOrderItemDetailById($shopcode,$shopid,$return_order_id,$order_item_id){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		 $sql =  "SELECT * from $shop_db.sales_order_return_items where return_order_id= $return_order_id AND  order_item_id = $order_item_id";
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


	 function add_to_sales_order_return($order_id,$increment_id,$customer_id,$return_request_due_date){

		//$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		//$main_db = DB_NAME; //Constant variable

		$status=0;
		$return_order_increment_id=$this->generate_return_order_transaction_id($order_id,$increment_id);
		
		$return_order_barcode=$return_order_increment_id;

		$params = array($order_id,$return_order_increment_id,$return_order_barcode,$customer_id,$status,$return_request_due_date,time(),$_SERVER['REMOTE_ADDR']);
		// echo $msgg =  "INSERT INTO sales_order_return (order_id,return_order_increment_id, return_order_barcode,customer_id, status,return_request_due_date,created_at,ip) VALUES (?, ?, ?,?, ?,?,?,?)";
		// print_R($params);
		// exit(json_encode($msgg));
		// die();
		$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO sales_order_return (order_id,return_order_increment_id, return_order_barcode,customer_id, status,return_request_due_date,created_at,ip) VALUES (?, ?, ?,?, ?,?,?,?)", $params);
		
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

	  public function add_to_sales_order_return_items($order_id,$return_order_id,$order_item_id,$qty_order,$qty_return,$price,$total_price,$barcode,$discount_amount='',$total_discount_amount='')
	{
		//$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		//$main_db = DB_NAME; //Constant variable

		$created_by_type=0;

		$params = array($order_id,$return_order_id,$order_item_id,$qty_order,$qty_return,$price,$total_price,$barcode,$discount_amount,$total_discount_amount,time(),$_SERVER['REMOTE_ADDR']);

		$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO sales_order_return_items (order_id,return_order_id,order_item_id ,qty_order ,qty_return,price,total_price,barcode,discount_amount,total_discount_amount,created_at,ip) VALUES (?, ?, ?,?, ?,?, ?, ?,?, ?,?,?)", $params);

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

	function get_order_items_column_data_for_sum($shopcode,$shopid,$order_id,$return_order_id,$column_name){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$sum_total=0;

		 $sql =  "SELECT SUM(oi.$column_name) as sum_total FROM $shop_db.sales_order_return_items as oi   where oi.return_order_id=$return_order_id ";
		 $row  = $this->dbl->dbl_conn->rawQueryOne($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $row['sum_total'];
			}else{
				return $sum_total;
			}
		}else{
			return $sum_total;
		}



	}

	function  update_return_order_column($return_order_id,$column_name,$column_value){

		//$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		//$main_db = DB_NAME; //Constant variable

		$params=array($column_value,time(),$return_order_id);

		$row  = $update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_order_return set $column_name = ?, updated_at = ? where  return_order_id = ?",$params);
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

	} // old-21-10-2021

	/*function  update_return_order_column($shopcode,$shopid,$return_order_id,$column_name,$column_value){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$params=array($column_value,time(),$return_order_id);

		//get data
		$returnStatus='';
		$param = array($return_order_id);
		$sql = "SELECT * FROM $shop_db.sales_order_return WHERE return_order_id = ?";
	 	$rowGet = $this->dbl->dbl_conn->rawQueryOne($sql,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				$returnStatus=$rowGet['status'];
				//return $rowGet;
			}else{
				return false;
			}
		}else{
			return false;
		}

		//end get data
	 	if($returnStatus==0){
	 		$row  = $update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.sales_order_return set $column_name = ?, updated_at = ? where  return_order_id = ?",$params);
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
	 		return false;
	 	}


	}*/

	function  update_return_order_item_column($shopcode,$shopid,$return_order_id,$order_item_id,$column_name,$column_value){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$params=array($column_value,time(),$return_order_id,$order_item_id);

		$row  = $update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.sales_order_return_items set $column_name = ?, updated_at = ? where  return_order_id = ? AND order_item_id = ? ",$params);
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

	function update_return_order_bank_detail($shopcode,$shopid,$return_order_id,$bank_name,$bank_branch,$ifsc_iban,$bic_swift,$bank_acc_no,$acc_holder_name){
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$params=array($bank_name,$bank_branch,$ifsc_iban,$bic_swift,$bank_acc_no,$acc_holder_name,time(),$return_order_id);

		$row  = $update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.sales_order_return set bank_name = ?,bank_branch = ?,ifsc_iban = ?,bic_swift = ?,bank_acc_no = ?,acc_holder_name = ?, updated_at = ? where  return_order_id = ?  ",$params);
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

	public function getOrdersDetailsByID($order_id)
 	{

		$param = array($order_id);
		$sql = "SELECT so.* FROM sales_order as so WHERE so.order_id = ?";
	 	$row = $this->dbl->dbl_conn->rawQueryOne($sql,$param);
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

 	public function getCustomerTypeMaster()
 	{

		$sql = "SELECT ctm.* FROM customers_type_master as ctm";
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

 	}

	function  cancel_order_request($order_id,$reason_for_cancel){

		$status =3;
		$cancel_by_customer=1;

		$params=array($status,$cancel_by_customer,$reason_for_cancel,time(),$order_id);

		$row  = $update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE sales_order set status = ?, cancel_by_customer = ?, cancel_reason = ?, cancel_date = ? where  order_id = ?",$params);
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

	public function createCancelCoupon($order_id,$coupon_amount,$apply_to)
	{

		$name = 'Refund Voucher for cancellation of order-'.$order_id;
		$description = 'Refund Voucher for cancellation of order-'.$order_id;
		$start_date = date('Y-m-d');
		$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
		$apply_condition = 'discount_on_mincartval';
		$apply_type = 'by_fixed';

		$params = array($name,$description,3,1,$start_date,$end_date,1,$apply_condition,$apply_type,$coupon_amount,$apply_to,0,1,1,time(),$_SERVER['REMOTE_ADDR']);

		$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO salesrule (name,description,type,coupon_type,start_date,end_date,status,apply_condition,apply_type,discount_amount,apply_to,min_cart_value,usage_per_customer,usge_per_coupon,created_at,ip) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", $params);

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

 	public function insert_salesrule_coupon($rules_id,$cancel_coupon_code)
	{
		$params = array($rules_id,$cancel_coupon_code,time(),$_SERVER['REMOTE_ADDR']);
		$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO salesrule_coupon (rule_id,coupon_code,created_at,ip) VALUES (?,?,?,?)", $params);
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

 	public function getOrdersItemsDetailsByID($order_id)
	{

		$param = array($order_id);
		$sql = "SELECT soi.* FROM sales_order_items as soi WHERE soi.order_id = ? AND soi.product_type != 'bundle' ORDER BY soi.item_id ASC";
		$Result = $this->dbl->dbl_conn->rawQuery($sql,$param);
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

	function  cancel_b2b_order_request($shopcode,$shopid,$b2b_order_id){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable
		$status =3;  //cancelled


		$params=array($status,time(),$b2b_order_id);

		$row  = $update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.b2b_orders set status = ?, updated_at = ? where  order_id = ?",$params);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				//return false;
			}
		}else{
			//return false;
		}

	}

	function  cancel_b2b_order_update_qty($shopcode,$shop_product_id,$product_order_qty){

		/*start*/
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$params=array($product_order_qty,$shop_product_id);

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
		/*end*/

	}

	public function getB2BOrderItems($sub_order_ids)
	{
		if (empty($sub_order_ids)) {
			return [];
		}

		$ids = implode(',', array_map('intval', $sub_order_ids));

		$sql = "SELECT order_id, product_name
				FROM b2b_order_items
				WHERE order_id IN ($ids)
				ORDER BY item_id ASC";

		$result = $this->dbl->dbl_conn->rawQuery($sql);

		if ($this->dbl->dbl_conn->getLastErrno() === 0 && $this->dbl->dbl_conn->count > 0) {
			// group by sub-order id
			return array_group($result, 'order_id');
		}

		return [];
	}


}
