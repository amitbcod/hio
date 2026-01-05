<?php
Class DbWishlistFeature{
	private $dbl;
	
	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}

	
	public function addtowishlist($shopcode,$customer_id,$prod_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$created_at = time();
		$ip = $_SERVER['REMOTE_ADDR'];
		$insert_login = "insert into $shop_db.wishlist_items (`customer_id`,`product_id`,`created_at`,`ip`) VALUES ('$customer_id',$prod_id,'$created_at','$ip') ";
		$query  = $this->dbl->dbl_conn->rawQueryOne($insert_login);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			return true;
		}else{
			return false;
		}
		
	}
	
	public function getProductExistWishlist($shopcode,$customer_id,$prod_id)
  	{		
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME;
  		$Row = "SELECT * FROM $shop_db.wishlist_items WHERE customer_id = '$customer_id' AND product_id = '$prod_id'";
		$query  = $this->dbl->dbl_conn->rawQueryOne($Row);
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
	
	public function getWishlistProductById($shopcode,$wishlist_id)
  	{		
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME;
  		$Row = "SELECT * FROM $shop_db.wishlist_items WHERE wishlist_id = '$wishlist_id'";
		$query  = $this->dbl->dbl_conn->rawQueryOne($Row);
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
	
	public function wishlist_deleteproduct($shopcode,$wishlist_id)
  	{		
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME;
  		$Row = "DELETE FROM $shop_db.wishlist_items WHERE wishlist_id = '$wishlist_id'";
		$query  = $this->dbl->dbl_conn->rawQueryOne($Row);
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

  	public function getProductExistWishlistByCustomerId($shopcode,$customer_id)
  	{		
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME;

		$param = array($customer_id);
		$Rows = $this->dbl->dbl_conn->rawQuery("SELECT * FROM $shop_db.wishlist_items WHERE customer_id = ?",$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $Rows;
			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

  	public function getproductDetailsById($shopcode,$shopid,$product_id,$lang_code='') 
  	{	
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$date = strtotime(date('d-m-Y'));
  		
		if($lang_code !=''){
			$param = array($lang_code,$product_id,$date,1);
			$query = "SELECT prod.*, mlp.name as other_lang_name FROM $shop_db.products as prod LEFT JOIN $shop_db.multi_lang_products as mlp ON (prod.id=mlp.product_id and mlp.lang_code=?) WHERE prod.id = ? AND prod.launch_date <= ? AND prod.status=?"; 
		}else{ 
  			$param = array($product_id,$date,1);
			$query = "SELECT prod.* FROM $shop_db.products as prod WHERE prod.id = ? AND prod.launch_date <= ? AND prod.status=?"; 
		}

  		$product_detail = $this->dbl->dbl_conn->rawQueryOne($query,$param);

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