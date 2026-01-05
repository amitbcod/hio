<?php
Class DbWishlistFeature{
	private $dbl;
	
	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}

	
	public function addtowishlist($customer_id,$prod_id)
	{
		$created_at = time();
		$ip = $_SERVER['REMOTE_ADDR'];
		$insert_login = "insert into wishlist_items (`customer_id`,`product_id`,`created_at`,`ip`) VALUES ('$customer_id',$prod_id,'$created_at','$ip') ";
		$query  = $this->dbl->dbl_conn->rawQueryOne($insert_login);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			return true;
		}else{
			return false;
		}
		
	}
	
	public function getProductExistWishlist($customer_id,$prod_id)
  	{		
  		$Row = "SELECT * FROM wishlist_items WHERE customer_id = '$customer_id' AND product_id = '$prod_id'";
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
	
	public function getWishlistProductById($wishlist_id)
  	{		
  		$Row = "SELECT * FROM wishlist_items WHERE wishlist_id = '$wishlist_id'";
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
	
	public function wishlist_deleteproduct($wishlist_id)
  	{		
  		$Row = "DELETE FROM wishlist_items WHERE wishlist_id = '$wishlist_id'";
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

  	public function getProductExistWishlistByCustomerId($customer_id)
  	{		
		$param = array($customer_id);
		$Rows = $this->dbl->dbl_conn->rawQuery("SELECT * FROM wishlist_items WHERE customer_id = ?",$param);
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

  	public function getproductDetailsById($product_id,$lang_code='') 
  	{	
  		$date = strtotime(date('d-m-Y'));
  		
		$param = array($product_id,$date,1);
		$query = "SELECT prod.* FROM products as prod WHERE prod.id = ? AND prod.launch_date <= ? AND prod.status=?"; 

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
