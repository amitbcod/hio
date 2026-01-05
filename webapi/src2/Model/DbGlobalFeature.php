<?php
Class DbGlobalFeature{
	private $dbl;
	
	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}
	
	public function get_custom_variable($shopcode,$identifier)
	{
		
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		$get_cms_page =  "SELECT * FROM $shop_db.custom_variables where  `identifier` = '$identifier'"; 
		$query  = $this->dbl->dbl_conn->rawQueryOne($get_cms_page);
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



?>