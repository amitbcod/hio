<?php
Class DbSearchFeature{
	private $dbl;
	
	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}

	
	public function save_search_term($shopcode,$search_term)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$created_at = time();
		$ip = $_SERVER['REMOTE_ADDR'];
		$insert_login = "insert into $shop_db.search_terms (`search_term`,`created_at`,`ip`) VALUES ('$search_term','$created_at','$ip') ";
		$query  = $this->dbl->dbl_conn->rawQueryOne($insert_login);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			return true;
		}else{
			return false;
		}
		
	}
	
	public function update_search_term($shopcode,$id,$popularity)
    {
    	$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$updated_at = time();
		$ipaddr = $_SERVER['REMOTE_ADDR'];
		$increasepop = $popularity + 1;
		$update_login = "update $shop_db.search_terms SET popularity = '$increasepop', updated_at = '$updated_at', ip = '$ipaddr' WHERE id = '$id'";
		$query  = $this->dbl->dbl_conn->rawQueryOne($update_login);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			return true;
		}else{
			return false;
		}
    }
	
	public function getSearchTermBySearch($shopcode,$search_term)
  	{		
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$params = array($search_term);
  		$Row = $this->dbl->dbl_conn->rawQueryOne("SELECT * FROM $shop_db.search_terms WHERE search_term = ?",$params);
		
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
	
	
	public function get_search_terms($shopcode,$search_term)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		$get_search_terms =  "SELECT * FROM $shop_db.search_terms where `search_term` LIKE '$search_term%' ORDER BY popularity DESC"; 
		$query  = $this->dbl->dbl_conn->rawQuery($get_search_terms);
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
	
	public function getPrevNextproductDetails($shopcode,$shopid,$product_id,$customer_type_id) 
  	{	
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$date = strtotime(date('d-m-Y'));
  		
  		$sub_query='';
  		$sub_query1='';
		if($customer_type_id > 2 )
		{
			$sub_query = "OR (prod.customer_type_ids='2')";
			$sub_query1 = "OR (prod1.customer_type_ids='2')";
		}
  		 $query = "(SELECT prod.id, prod.url_key FROM ".$shop_db.".products as prod WHERE prod.id < ".$product_id." AND prod.launch_date <= ".$date." AND ((prod.product_type='simple')OR(prod.product_type='configurable')) AND prod.status='1' AND ((FIND_IN_SET($customer_type_id,prod.customer_type_ids)) OR (prod.customer_type_ids='0') ".$sub_query.")  ORDER BY prod.id DESC LIMIT 1) UNION (SELECT prod1.id, prod1.url_key FROM ".$shop_db.".products as prod1 WHERE prod1.id > ".$product_id." AND prod1.launch_date <=".$date." AND ((prod1.product_type='simple')OR(prod1.product_type='configurable')) AND prod1.status='1' AND ((FIND_IN_SET($customer_type_id,prod1.customer_type_ids)) OR (prod1.customer_type_ids='0') ".$sub_query1.") ORDER BY prod1.id ASC LIMIT 1)";

		$product_detail = $this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $product_detail;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}


	  public function getPrevNextproductDetailsNew($shopcode,$shopid,$product_id,$customer_type_id,$categoryID) 
  	{	
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$date = strtotime(date('d-m-Y'));
  		
  		$sub_query='';
  		$sub_query1='';
		if($customer_type_id > 2 )
		{
			$sub_query = "OR (prod.customer_type_ids='2')";
			$sub_query1 = "OR (prod1.customer_type_ids='2')";
		}
  		$query = "(SELECT prod.id, prod.url_key FROM ".$shop_db.".products as prod INNER JOIN $shop_db.products_category ON prod.id=$shop_db.products_category.product_id  WHERE products_category.category_ids = ".$categoryID." AND prod.id < ".$product_id." AND prod.launch_date <= ".$date." AND ((prod.product_type='simple')OR(prod.product_type='configurable')) AND prod.status='1' AND prod.remove_flag='0'  AND ((FIND_IN_SET($customer_type_id,prod.customer_type_ids)) OR (prod.customer_type_ids='0') ".$sub_query.")  ORDER BY prod.id DESC LIMIT 1) UNION (SELECT prod1.id, prod1.url_key FROM ".$shop_db.".products as prod1 INNER JOIN $shop_db.products_category ON prod1.id=$shop_db.products_category.product_id WHERE products_category.category_ids = ".$categoryID." AND prod1.id > ".$product_id." AND prod1.launch_date <=".$date." AND ((prod1.product_type='simple')OR(prod1.product_type='configurable')) AND prod1.status='1' AND prod1.remove_flag='0' AND ((FIND_IN_SET($customer_type_id,prod1.customer_type_ids)) OR (prod1.customer_type_ids='0') ".$sub_query1.") ORDER BY prod1.id ASC LIMIT 1)";

		//  echo $query;exit;
		$product_detail = $this->dbl->dbl_conn->rawQuery($query);


		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $product_detail;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	} 
	
}	
?>