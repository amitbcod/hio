<?php
class DbSearchFeature
{
	private $dbl;

	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}


	public function save_search_term($search_term)
	{
		$created_at = time();
		$ip = $_SERVER['REMOTE_ADDR'];
		$insert_login = "insert into search_terms (`search_term`,`created_at`,`ip`) VALUES ('$search_term','$created_at','$ip') ";
		$query  = $this->dbl->dbl_conn->rawQueryOne($insert_login);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			return true;
		} else {
			return false;
		}
	}

	public function update_search_term($id, $popularity)
	{
		$updated_at = time();
		$ipaddr = $_SERVER['REMOTE_ADDR'];
		$increasepop = $popularity + 1;
		$update_login = "update search_terms SET popularity = '$increasepop', updated_at = '$updated_at', ip = '$ipaddr' WHERE id = '$id'";
		$query  = $this->dbl->dbl_conn->rawQueryOne($update_login);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			return true;
		} else {
			return false;
		}
	}

	public function getSearchTermBySearch($search_term)
	{
		$params = array($search_term);
		$Row = $this->dbl->dbl_conn->rawQueryOne("SELECT * FROM search_terms WHERE search_term = ?", $params);

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


	public function get_search_terms($search_term)
	{
		$get_search_terms =  "SELECT * FROM search_terms where `search_term` LIKE '%$search_term%' ORDER BY popularity DESC";
		$query  = $this->dbl->dbl_conn->rawQuery($get_search_terms);
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
	public function get_search_terms_name($search_term, $categoryIdsarr = array())
	{
		$date =  time();
		$get_search_terms =  "SELECT `id`, `name` AS search_term FROM products where (`name` LIKE '$search_term%'  OR  `description` LIKE '$search_term%' OR  `meta_title` LIKE '$search_term%' OR `search_keywords` LIKE '$search_term%' OR `meta_keyword` LIKE '$search_term%' OR `meta_description` LIKE '$search_term%') and `status` =1 and `approval_status` = 1 and `remove_flag` = 0 and `launch_date` <= '$date'
		group by `name` ORDER BY `name` DESC ";

		//$get_search_terms =  "SELECT prod.`id`, prod.`name` AS search_term FROM products as prod where (prod.`name` LIKE '$search_term%'  OR  prod.`description` LIKE '$search_term%' OR  prod.`meta_title` LIKE '$search_term%' OR prod.`search_keywords` LIKE '$search_term%' OR prod.`meta_keyword` LIKE '$search_term%' OR prod.`meta_description` LIKE '$search_term%') and prod.`status` =1 and prod.`remove_flag` = 0 and prod.`launch_date` <= '$date' group by prod.`name` ORDER BY prod.`name` DESC ";

		// $get_search_terms =  "SELECT prod.`id`, prod.`name` AS search_term FROM products as prod where (prod.`name` LIKE '%$search_term%'  OR  prod.`description` LIKE '$search_term%' OR  prod.`meta_title` LIKE '$search_term%' OR prod.`search_keywords` LIKE '$search_term%' OR prod.`meta_keyword` LIKE '$search_term%' OR prod.`meta_description` LIKE '$search_term%') and prod.`status` =1 and prod.`remove_flag` = 0 and prod.`launch_date` <= '$date' ORDER BY prod.`name` DESC ";
		
		$query  = $this->dbl->dbl_conn->rawQuery($get_search_terms);
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

	public function get_search_terms_name_cat($search_term, $categoryIdsarr = array())
	{
		$date =  time();
		// $get_search_terms =  "SELECT `id`, `name` AS search_term FROM products where (`name` LIKE '$search_term%'  OR  `description` LIKE '$search_term%' OR  `meta_title` LIKE '$search_term%' OR `search_keywords` LIKE '$search_term%' OR `meta_keyword` LIKE '$search_term%' OR `meta_description` LIKE '$search_term%') and `status` =1 and `remove_flag` = 0 and `launch_date` <= '$date'
		// group by `name` ORDER BY `name` DESC ";
		// $categoryIdsarr = array();
		if (isset($categoryIdsarr) && count($categoryIdsarr) > 0) {
			$categoryIdsarr_val = "'" . implode("','", $categoryIdsarr) . "'";

			$get_search_terms =  "SELECT prod.`id`, prod.`name` AS search_term FROM products as prod INNER JOIN products_category ON prod.id=products_category.product_id where (prod.`name` LIKE '%$search_term%'  OR  prod.`description` LIKE '%$search_term%' OR  prod.`meta_title` LIKE '%$search_term%' OR prod.`search_keywords` LIKE '%$search_term%' OR prod.`meta_keyword` LIKE '$search_term%' OR prod.`meta_description` LIKE '%$search_term%') OR products_category.category_ids IN ($categoryIdsarr_val) and prod.`status` =1 and prod.`remove_flag` = 0 and prod.`launch_date` <= '$date' group by prod.`name` ORDER BY prod.`name` DESC ";
		} else {
			$get_search_terms =  "SELECT prod.`id`, prod.`name` AS search_term FROM products as prod where (prod.`name` LIKE '%$search_term%'  OR  prod.`description` LIKE '%$search_term%' OR  prod.`meta_title` LIKE '%$search_term%' OR prod.`search_keywords` LIKE '%$search_term%' OR prod.`meta_keyword` LIKE '%$search_term%' OR prod.`meta_description` LIKE '%$search_term%') and prod.`status` =1 and prod.`remove_flag` = 0 and prod.`launch_date` <= '$date' group by prod.`name` ORDER BY prod.`name` DESC ";
		}
		$query  = $this->dbl->dbl_conn->rawQuery($get_search_terms);
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

	public function getPrevNextproductDetails($product_id)
	{

		$date = strtotime(date('d-m-Y'));
		$query = "(SELECT prod.id, prod.url_key FROM products as prod WHERE prod.id < " . $product_id . " AND prod.launch_date <= " . $date . " AND ((prod.product_type='simple')OR(prod.product_type='configurable')) AND prod.status='1' AND prod.remove_flag='0' ORDER BY prod.id DESC LIMIT 1) UNION (SELECT prod1.id, prod1.url_key FROM products as prod1 WHERE prod1.id > " . $product_id . " AND prod1.launch_date <=" . $date . " AND ((prod1.product_type='simple')OR(prod1.product_type='configurable')) AND prod1.status='1' AND prod1.remove_flag='0' ORDER BY prod1.id ASC LIMIT 1)";

		//  echo $query;
		//  exit;
		$product_detail = $this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $product_detail;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	public function getPrevNextproductDetailsNew($product_id, $categoryID)
	{
		// $main_db = DB_NAME; //Constant variable

		$date = strtotime(date('d-m-Y'));

		$sub_query = '';
		$sub_query1 = '';

		// echo "jain";
		// exit;

		$query = "(SELECT prod.id, prod.url_key FROM products as prod INNER JOIN products_category ON prod.id=products_category.product_id  WHERE products_category.category_ids = " . $categoryID . " AND prod.id < " . $product_id . " AND prod.launch_date <= " . $date . " AND ((prod.product_type='simple')OR(prod.product_type='configurable')) AND prod.status='1' AND prod.remove_flag='0'   ORDER BY prod.id DESC LIMIT 1) UNION (SELECT prod1.id, prod1.url_key FROM products as prod1 INNER JOIN products_category ON prod1.id=products_category.product_id WHERE products_category.category_ids = " . $categoryID . " AND prod1.id > " . $product_id . " AND prod1.launch_date <=" . $date . " AND ((prod1.product_type='simple')OR(prod1.product_type='configurable')) AND prod1.status='1' AND prod1.remove_flag='0' ORDER BY prod1.id ASC LIMIT 1)";

		//  echo $query;exit;
		$product_detail = $this->dbl->dbl_conn->rawQuery($query);


		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $product_detail;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getPrevNextproductDetailsNewArrivals($shopcode, $shopid, $product_id, $customer_type_id, $badge = '')
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$date = strtotime(date('d-m-Y'));

		$sub_query = '';
		$sub_query1 = '';
		if ($customer_type_id > 2) {
			$sub_query = "OR (prod.customer_type_ids='2')";
			$sub_query1 = "OR (prod1.customer_type_ids='2')";
		}
		$badge_query = '';
		if ($badge != '') {
			$Date = date('Y-m-d');
			$from_date = strtotime($Date . ' - 45 days');
			$badge_query = 'AND prod.launch_date >= ' . $from_date;
			$badge_query1 = 'AND prod1.launch_date >= ' . $from_date;
		}
		$query = "(SELECT prod.id, prod.url_key FROM " . $shop_db . ".products as prod WHERE prod.id < " . $product_id . " AND prod.launch_date <= " . $date . " $badge_query AND ((prod.product_type='simple')OR(prod.product_type='configurable')) AND prod.status='1' AND prod.remove_flag='0' AND ((FIND_IN_SET($customer_type_id,prod.customer_type_ids)) OR (prod.customer_type_ids='0') " . $sub_query . ")  ORDER BY prod.id DESC LIMIT 1) UNION (SELECT prod1.id, prod1.url_key FROM " . $shop_db . ".products as prod1 WHERE prod1.id > " . $product_id . " AND prod1.launch_date <=" . $date . " $badge_query1 AND ((prod1.product_type='simple')OR(prod1.product_type='configurable')) AND prod1.status='1' AND prod1.remove_flag='0' AND ((FIND_IN_SET($customer_type_id,prod1.customer_type_ids)) OR (prod1.customer_type_ids='0') " . $sub_query1 . ") ORDER BY prod1.id ASC LIMIT 1)";
		$product_detail = $this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $product_detail;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}
