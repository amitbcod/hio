<?php
Class DbProductFeature{
	private $dbl;

	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}




//LEFT JOIN multi_lang_category as mlc ON (category.id=mlc.category_id and mlc.lang_code=?)

public function getSubscriptionVariants(){
    $param = array(6);
	$query = "SELECT eav.* FROM eav_attributes LEFT JOIN eav_attributes_options as eav ON (eav_attributes.id=eav.attr_id)
	 WHERE eav_attributes.id = ? ";

	$subscription_variant = $this->dbl->dbl_conn->rawQuery($query,$param);
	 if ($this->dbl->dbl_conn->getLastErrno() === 0){

			if ($this->dbl->dbl_conn->count > 0){
				
				return $subscription_variant;
			}else{
				return false;
			}
		}else{
			return false;
		}


}



	public function productMultipleVariantOptions($shopcode,$shopid,$product_id,$variant_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
  		$product_ids_string = implode(',',$product_id);
  		$variant_ids_string = implode(',',$variant_id);
		$query = "SELECT DISTINCT eav_attr_opt.attr_options_name,pv.parent_id,pv.product_id,pv.attr_value FROM $shop_db.products_variants as pv INNER JOIN $main_db.eav_attributes_options as eav_attr_opt ON eav_attr_opt.id = pv.attr_value WHERE pv.parent_id IN($product_ids_string) AND pv.attr_id IN ($variant_ids_string) ";

		// print_r($query);exit;
		//$variant_option = $this->dbl->dbl_conn->query($query);
		$variant_option = $this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $variant_option;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	public function productMultipleVariantOptionsNew($product_id,$variant_id)
  	{
  		$product_ids_string = implode(',',$product_id);
  		$variant_ids_string = implode(',',$variant_id);
		$query = "SELECT DISTINCT eav_attr_opt.attr_options_name,pv.parent_id,pv.product_id,pv.attr_value,bc.color_name,bc.square_color FROM products_variants as pv INNER JOIN eav_attributes_options as eav_attr_opt ON eav_attr_opt.id = pv.attr_value INNER JOIN base_colors_variants as bcv ON bcv.variant_option_id = pv.attr_value INNER JOIN base_colors as bc ON bcv.base_color_id = bc.id WHERE pv.parent_id IN($product_ids_string) AND pv.attr_id IN ($variant_ids_string) ";

		// print_r($query);exit;
		//$variant_option = $this->dbl->dbl_conn->query($query);
		$variant_option = $this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $variant_option;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

	public function configProductVariantProduct($product_id)
  	{
  		$product_ids_string = implode(',',$product_id);
        $param = array(6);

	 //    $query = "SELECT eav_attr.id,eav_attr.attr_code,eav_attr.attr_name,pv.product_id FROM $shop_db.products_variants as pv 
		// INNER JOIN $main_db.eav_attributes as eav_attr ON eav_attr.id=pv.attr_id 
		// INNER JOIN $shop_db.products_variants_master as pv_m ON pv_m.attr_id = eav_attr.id 
		// WHERE pv.product_id OR pv_m.product_id IN($product_ids_string) AND pv.attr_id OR pv_m.attr_id =? ";
			// print_r($query);exit;

		$query = "SELECT DISTINCT pv_m.product_id,eav_attr.id, eav_attr.attr_code FROM products_variants_master as pv_m INNER JOIN eav_attributes as eav_attr ON eav_attr.id=pv_m.attr_id WHERE pv_m.product_id IN($product_ids_string) AND pv_m.attr_id=? ";

		// INNER JOIN $main_db.eav_attributes_options evp ON evp.id=pv.attr_value AND ev.id = evp.attr_id WHERE pv.product_id IN($product_ids_string) AND eav_attr.id=? ;
	  
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



 	public function getCategoryDetails($categoryslug='',$lang_code='')
  	{
  		$type = explode("/", $categoryslug);

		if($lang_code!=''){
			$param = array($lang_code,$type[0]);

			$query = "SELECT category.*,mlc.cat_name as lang_cat_name FROM category LEFT JOIN multi_lang_category as mlc ON (category.id=mlc.category_id and mlc.lang_code=?) WHERE slug = ? AND cat_level=0 AND status=1";

		}else{
  			$param = array($type[0]);

			$query = "SELECT * FROM category WHERE slug = ? AND cat_level=0 AND status=1";
		}

  		$category = $this->dbl->dbl_conn->rawQueryOne($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				if(sizeof($type) > 1){

					if($lang_code!=''){
						$param1 = array($lang_code,$type[1],$category['id']);

						$query1 = "SELECT category.*,mlc.cat_name as lang_cat_name FROM category LEFT JOIN multi_lang_category as mlc ON (category.id=mlc.category_id and mlc.lang_code=?) WHERE slug = ? AND cat_level=1 AND parent_id=? AND status=1";
					}else{

						$param1 = array($type[1],$category['id']);

						$query1 = "SELECT * FROM category WHERE slug = ? AND cat_level=1 AND parent_id=? AND status=1";
					}

					$category1 = $this->dbl->dbl_conn->rawQueryOne($query1,$param1);

					if ($this->dbl->dbl_conn->getLastErrno() === 0){
						if ($this->dbl->dbl_conn->count > 0){
							if(sizeof($type) > 2){

								if($lang_code!=''){
									$param2 = array($lang_code,$type[2],$category1['id'],$category['id']);

									$query2 = "SELECT category.*,mlc.cat_name as lang_cat_name FROM category LEFT JOIN multi_lang_category as mlc ON (category.id=mlc.category_id and mlc.lang_code=?) WHERE slug = ? AND cat_level=2 AND parent_id=? AND main_parent_id = ? AND status=1";
								}else{
									$param2 = array($type[2],$category1['id'],$category['id']);

									$query2 = "SELECT * FROM category WHERE slug = ? AND cat_level=2 AND parent_id=? AND main_parent_id = ? AND status=1";
								}
								$category2 = $this->dbl->dbl_conn->rawQueryOne($query2,$param2);

								if ($this->dbl->dbl_conn->getLastErrno() === 0){
									if ($this->dbl->dbl_conn->count > 0){
										return $category2;
									}else{
										return false;
									}
								}else{
									return false;
								}
							}else{
								return $category1;
							}

						}else{
							return false;
						}
					}else{
						return false;
					}
				}else{
					return $category;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

  	public function productListing($categoryid='',$options='',$gender=array(),$subscription =array(),$price_range=array(),$variant_id_arr=array(),$variant_attr_value_arr=array(),$attribute_arr=array(),$search_term='',$page='',$page_size='')
  	{
		$where_gender = '';
		if(!empty($gender) && count($gender)>0) {

			$str = implode("|",$gender);
			$where_gender = 'AND CONCAT(",",prod.gender,",") REGEXP ",('.$str.'),"';

			// $wg_case=array();
			// $where_gender .=  ' AND ( ';
			// foreach($gender as $key=>$val){
			// 	$wg_case[]="FIND_IN_SET('".$val."',prod.gender)";
			// }
			// $where_gender .=  implode(' OR ',$wg_case);
			// $where_gender .=  ' )';
		}

		$where_subscription = '';
		if(!empty($subscription) && count($subscription) > 0){
			$subs = implode(",",$subscription);
			
		}

		$where_price = '';
		/* if(!empty($price_range)) {
			$price_range[0]=($price_range[0]<=0 || $price_range[0]=='')?0:$price_range[0];

			$where_price = 'AND ( prod.product_type IN ("simple") AND prod.webshop_price IS NOT null AND  (prod.webshop_price BETWEEN '.$price_range[0].' AND '.$price_range[1].') OR (prod.product_type IN ("configurable") AND exists(SELECT 1 FROM '.$shop_db.'.products WHERE ('.$shop_db.'.products.parent_id = prod.id AND '.$shop_db.'.products.webshop_price IS NOT null AND ('.$shop_db.'.products.webshop_price BETWEEN '.$price_range[0].' AND '.$price_range[1].' ) ))))';
		}*/

		$where_variant="";
		$distinct="";
		if(!empty($variant_id_arr) && !empty($variant_attr_value_arr)) {
			$str_id = "'".implode("','",$variant_id_arr)."'";
			$str_value = "'".implode("','",$variant_attr_value_arr)."'";

			$where_variant = 'INNER JOIN products_variants as prv ON prv.parent_id=products_category.product_id AND prv.attr_id IN ('.$str_id.') AND prv.attr_value IN ('.$str_value.') INNER JOIN products_inventory as pin ON pin.product_id=prv.product_id ';
			$distinct = 'DISTINCT prv.parent_id,';

			/*$inv_check_where =' AND CASE WHEN prod.product_type = "configurable" THEN CASE WHEN ((SELECT product_inv_type FROM '.$shop_db.'.products WHERE id=pin.product_id)="virtual" && pin.available_qty <=0) || ((SELECT product_inv_type FROM '.$shop_db.'.products WHERE id=pin.product_id)="dropship") THEN (SELECT available_qty FROM '.$shop_db_new.'prod.shop_id.products_inventory WHERE product_id=(SELECT shop_product_id  FROM products WHERE `id`=pin.product_id))  ELSE pin.available_qty END ELSE CASE WHEN ((prod.product_inv_type ="virtual" && (SELECT available_qty FROM '.$shop_db.'.products_inventory WHERE product_id=prod.id) <=0) || prod.product_inv_type = "dropship") THEN (SELECT available_qty FROM '.$shop_db_new.'prod.shop_id.products_inventory WHERE product_id=prod.shop_product_id) ELSE (SELECT available_qty FROM '.$shop_db.'.products_inventory WHERE product_id=prod.id) END END > 0 ';*/

			$inv_check_where =' AND CASE WHEN prod.product_type = "configurable" THEN pin.available_qty ELSE (SELECT available_qty FROM products_inventory WHERE product_id=prod.id) END > 0 ';

		}else{
			$inv_check_where =' ';
		}

		$where_attribute="";

		$where_attr='';
		if(!empty($attribute_arr)) {
			/*
			$str_attrid = "'".implode("','",$attribute_arr)."'";
			$where_attribute = 'INNER JOIN '.$shop_db.'.products_attributes as p_attr ON p_attr.product_id='.$shop_db.'.products_category.product_id AND p_attr.attr_id IN ('.$str_attrid.')';

			*/


			$wg_case=array();
			$where_attr .=  ' AND ( ';
			foreach($attribute_arr as $key=>$val){
			$wg_case[]="FIND_IN_SET('".$val."',p_attr.attr_value)";
			}
			$where_attr .=  implode(' OR ',$wg_case);
			$where_attr .=  ' )';

			$where_attribute = 'INNER JOIN products_attributes as p_attr ON p_attr.product_id=products_category.product_id  '.$where_attr;
			$distinct = 'DISTINCT p_attr.product_id,';
		}

		$where_search="";
		if($search_term!='') {
			//$where_search = 'prod.name LIKE "%'.$search_term.'%" AND';
			$where_search = '(prod.name LIKE "%'.$search_term.'%" OR prod.product_code LIKE "%'.$search_term.'%" OR prod.highlights LIKE "%'.$search_term.'%" OR prod.search_keywords LIKE "%'.$search_term.'%" OR prod.promo_reference LIKE "%'.$search_term.'%") AND';
		}

		$popular_count = '';
		$popular = '';
		$price_sorting = ''; //
		$special_price_sorting = '';
		$date_sp = strtotime(date('Y-m-d'));
		
  		if($options=='price_des'){			
			$price_sorting = ',( select min(webshop_price) from products where parent_id = prod.id) AS price_sorting_configurable,(SELECT min(webshop_price) FROM products WHERE id = prod.id) AS price_sorting_simple';
			
			$special_price_sorting = ',(SELECT min(special_price) FROM products_special_prices WHERE (product_id = prod.id) AND (special_price_from <= '.$date_sp.' AND special_price_to >= '.$date_sp.')) AS special_price_sorting_simple, (SELECT min(special_price) FROM products_special_prices WHERE product_id IN (SELECT id FROM products WHERE parent_id = prod.id)	AND (special_price_from <= '.$date_sp.' AND special_price_to >= '.$date_sp.')) AS special_price_sorting_configurable';
			
			$order_by = ' ORDER BY CASE WHEN product_type = "configurable" THEN	CASE WHEN special_price_sorting_configurable IS NULL THEN price_sorting_configurable ELSE special_price_sorting_configurable END ELSE CASE WHEN special_price_sorting_simple IS NULL THEN price_sorting_simple ELSE	special_price_sorting_simple END END DESC';	
  		} else if($options=='price_asc'){ 		
			$price_sorting = ',( select min(webshop_price) from products where parent_id = prod.id) AS price_sorting_configurable,(SELECT min(webshop_price) FROM products WHERE id = prod.id) AS price_sorting_simple';  
			
  			$special_price_sorting = ',(SELECT min(special_price) FROM products_special_prices WHERE (product_id = prod.id) AND (special_price_from <= '.$date_sp.' AND special_price_to >= '.$date_sp.')) AS special_price_sorting_simple, (SELECT min(special_price) FROM products_special_prices WHERE product_id IN (SELECT id FROM products WHERE parent_id = prod.id)	AND (special_price_from <= '.$date_sp.' AND special_price_to >= '.$date_sp.')) AS special_price_sorting_configurable';
			
  			$order_by = ' ORDER BY CASE WHEN product_type = "configurable" THEN	CASE WHEN special_price_sorting_configurable IS NULL THEN price_sorting_configurable ELSE special_price_sorting_configurable END ELSE CASE WHEN special_price_sorting_simple IS NULL THEN price_sorting_simple ELSE	special_price_sorting_simple END END ASC';
			 
  		}else if($options=='popular'){
  			//$popular_count = ',COUNT("s.*") as popular';
  			//$popular = 'LEFT JOIN '.$shop_db.'.sales_order_items as s ON ((s.product_id = prod.id AND prod.product_type="simple") OR (s.parent_product_id = prod.id AND prod.product_type="configurable"))';
  			//$order_by = ' ORDER BY popular DESC';

			$order_by = ' ORDER BY prod.launch_date DESC, prod.id DESC ';
  		}else{
			$order_by = ' ORDER BY prod.launch_date DESC, prod.id DESC ';
  		}

		if(!empty($page) || !empty($page_size))
		{
			$limit=" LIMIT $page , $page_size";
		}else{
			$limit=" ";
		}

  		$date = strtotime(date('d-m-Y'));
		//echo $date;
  		$param = array($date,1,0);
		//echo date('d-m-Y');
		// $sql = "SELECT ".$distinct." prod.*".$popular_count." FROM ".$shop_db.".products as prod INNER JOIN ".$shop_db.".products_category ON prod.id=".$shop_db.".products_category.product_id AND FIND_IN_SET(".$categoryid.",products_category.category_ids) ".$where_variant.$where_attribute.$popular." WHERE prod.launch_date <= ".$date." AND ((FIND_IN_SET(".$customer_type_id.",prod.customer_type_ids)) OR (prod.customer_type_ids='0')) AND (prod.product_type IN ('simple','configurable')) ".$where_gender.$where_price ."AND prod.status='1' AND prod.remove_flag='0' GROUP BY prod.id".$order_by.$limit;
		// echo $sql;exit;

		$lang_select_data='';
		$lang_query='';

		$prod_select_list='prod.name,prod.gender,prod.price,prod.id,prod.product_inv_type,prod.special_price,prod.special_price_from,prod.special_price_to,prod.webshop_price,prod.url_key,prod.product_type,prod.product_code,prod.highlights,prod.search_keywords,prod.promo_reference,prod.launch_date,prod.status,prod.remove_flag,prod.base_image,prod.coming_soon_flag';

  		if($categoryid!=''){
  			
			$query = "SELECT $distinct $prod_select_list $popular_count $lang_select_data $price_sorting $special_price_sorting FROM products as prod $lang_query INNER JOIN products_category ON prod.id=products_category.product_id AND FIND_IN_SET($categoryid,products_category.category_ids) $where_variant $where_attribute $popular WHERE prod.launch_date <= ? AND (prod.product_type IN ('simple','configurable','bundle')) $where_gender $where_price AND prod.status=? AND prod.remove_flag=? $inv_check_where GROUP BY prod.id $order_by $limit ";
			//echo 'if';die();
			//echo $query; die();
				// $query = "SELECT $distinct prod.* $popular_count $lang_select_data $price_sorting $special_price_sorting FROM $shop_db.products as prod $lang_query INNER JOIN $shop_db.products_category ON prod.id=$shop_db.products_category.product_id AND FIND_IN_SET($categoryid,products_category.category_ids) $where_variant $where_attribute $popular WHERE prod.launch_date <= ? AND ((FIND_IN_SET($customer_type_id,prod.customer_type_ids)) OR (prod.customer_type_ids='0') $sub_query) AND (prod.product_type IN ('simple','configurable')) $where_gender $where_price AND prod.status=? AND prod.remove_flag=? $inv_check_where GROUP BY prod.id $order_by $limit ";
  		}else{
			$lang_select_data = ''; $price_sorting = ''; $special_price_sorting = ''; $lang_query = ''; $popular = '';
				$query = "SELECT $prod_select_list $popular_count $lang_select_data $price_sorting $special_price_sorting FROM products as prod $lang_query $popular WHERE $where_search prod.launch_date <= ? AND 
				(prod.product_type IN ('simple','configurable','bundle')) AND prod.status=? AND prod.remove_flag=? $order_by  $limit ";



				//echo $this->dbl->dbl_conn->getLastQuery();die();
				// $query = "SELECT prod.* $popular_count $lang_select_data $price_sorting $special_price_sorting FROM $shop_db.products as prod $lang_query $popular WHERE $where_search prod.launch_date <= ? AND ((FIND_IN_SET($customer_type_id,prod.customer_type_ids)) OR (prod.customer_type_ids='0') $sub_query) AND (prod.product_type IN ('simple','configurable')) AND prod.status=? AND prod.remove_flag=? $order_by  $limit ";
			
  		}
  	// print_r($param);
		// echo $query;
		// exit();
//		echo "/n";
		$product_list = $this->dbl->dbl_conn->rawQuery($query,$param);
		//print_r($this->dbl->dbl_conn->count);exit();
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $product_list;

			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

	public function productListingCount($categoryid='',$options='',$gender=array(),$price_range=array(),$variant_id_arr=array(),$variant_attr_value_arr=array(),$attribute_arr=array(),$search_term='',$page='',$page_size='')
  	{
  		$where_gender = '';
		if(!empty($gender)) {
			$str = implode("|",$gender);
			$where_gender = 'AND CONCAT(",",prod.gender,",") REGEXP ",('.$str.'),"';

		}

		$where_price = '';

		$where_variant="";
		$distinct="";
		if(!empty($variant_id_arr) && !empty($variant_attr_value_arr)) {
			$str_id = "'".implode("','",$variant_id_arr)."'";
			$str_value = "'".implode("','",$variant_attr_value_arr)."'";

			if ($categoryid!='') {
				$where_variant = 'INNER JOIN products_variants as prv ON prv.parent_id=products_category.product_id AND prv.attr_id IN ('.$str_id.') AND prv.attr_value IN ('.$str_value.') INNER JOIN products_inventory as pin ON pin.product_id=prv.product_id ';
				$distinct = 'DISTINCT prv.parent_id,';
			} else {
				$where_variant = 'INNER JOIN products_variants as prv ON prv.parent_id=prod.id AND prv.attr_id IN ('.$str_id.') AND prv.attr_value IN ('.$str_value.') INNER JOIN products_inventory as pin ON pin.product_id=prv.product_id ';
				$distinct = 'DISTINCT prv.parent_id,';
			}

			$inv_check_where =' AND CASE WHEN prod.product_type = "configurable" THEN pin.available_qty ELSE (SELECT available_qty FROM products_inventory WHERE product_id=prod.id) END > 0 ';
		}else{
			$inv_check_where =' ';
		}

		$where_attribute="";
		$where_attr='';
		if(!empty($attribute_arr)) {

			$wg_case=array();
			$where_attr .=  ' AND ( ';
			foreach($attribute_arr as $key=>$val){
			$wg_case[]="FIND_IN_SET('".$val."',p_attr.attr_value)";
			}
			$where_attr .=  implode(' OR ',$wg_case);
			$where_attr .=  ' )';


			if ($categoryid!='') {
				$where_attribute = 'INNER JOIN products_attributes as p_attr ON p_attr.product_id=products_category.product_id  '.$where_attr;
				$distinct = 'DISTINCT p_attr.product_id,';
			} else {
				$where_attribute = 'INNER JOIN products_attributes as p_attr ON p_attr.product_id=prod.id  '.$where_attr;
				$distinct = 'DISTINCT p_attr.product_id,';
			}

		}

		$where_search="";
		if($search_term!='') {
			$where_search = '(prod.name LIKE "%'.$search_term.'%" OR prod.product_code LIKE "%'.$search_term.'%" OR prod.highlights LIKE "%'.$search_term.'%" OR prod.search_keywords LIKE "%'.$search_term.'%" OR prod.promo_reference LIKE "%'.$search_term.'%") AND';
		}

		$popular_count = '';
		$popular = '';
  		if($options=='price_des'){
  			$order_by = ' ORDER BY prod.webshop_price DESC';
  		}else if($options=='price_asc'){
  			$order_by = ' ORDER BY prod.webshop_price ASC';
  		}else if($options=='popular'){
			$order_by = ' ORDER BY prod.launch_date DESC, prod.id DESC ';
  		}else{
  			$order_by = ' ORDER BY prod.launch_date DESC, prod.id DESC ';
  		}

		$limit=" ";


  		$date = strtotime(date('d-m-Y'));
  		$param = array($date,1,0);
  		if($categoryid!=''){
  			$query = "SELECT count(*) as total_row FROM products as prod INNER JOIN products_category ON prod.id=products_category.product_id AND FIND_IN_SET($categoryid,products_category.category_ids) $where_variant $where_attribute $popular WHERE prod.launch_date <= ? AND (prod.product_type IN ('simple','configurable','bundle')) $where_gender $where_price AND prod.status=? AND prod.remove_flag=? $inv_check_where GROUP BY prod.id $order_by  ";
  		}else{
			$query = "SELECT  count(*) as total_row FROM products as prod $where_variant $where_attribute $popular WHERE $where_search prod.launch_date <= ? AND (prod.product_type IN ('simple','configurable','bundle')) $where_gender $where_price AND prod.status=? AND prod.remove_flag=? $inv_check_where GROUP BY prod.id $order_by   ";
  		}

		// echo $query;exit;

		$product_list = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return count($product_list);
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	public function productListingFilteredpid($shopcode,$shop_id,$categoryid='',$options='',$customer_type_id='',$gender=array(),$price_range=array(),$variant_id_arr=array(),$variant_attr_value_arr=array(),$attribute_arr=array(),$search_term='',$page='',$page_size='')
  	{

  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$where_gender = '';
		if(!empty($gender)) {
			$str = implode("|",$gender);
			$where_gender = 'AND CONCAT(",",prod.gender,",") REGEXP ",('.$str.'),"';

			/*
			$wg_case=array();
			$where_gender .=  ' AND ( ';
			foreach($gender as $key=>$val){
				$wg_case[]="FIND_IN_SET('".$val."',prod.gender)";
			}
			$where_gender .=  implode(' OR ',$wg_case);
			$where_gender .=  ' )';
			*/
		}

		$where_price = '';
		/* if(!empty($price_range)) {
			$price_range[0]=($price_range[0]<=0 || $price_range[0]=='')?0:$price_range[0];

			$where_price = 'AND ( prod.product_type IN ("simple") AND prod.webshop_price IS NOT null AND  (prod.webshop_price BETWEEN '.$price_range[0].' AND '.$price_range[1].') OR (prod.product_type IN ("configurable") AND exists(SELECT 1 FROM '.$shop_db.'.products WHERE ('.$shop_db.'.products.parent_id = prod.id AND '.$shop_db.'.products.webshop_price IS NOT null AND ('.$shop_db.'.products.webshop_price BETWEEN '.$price_range[0].' AND '.$price_range[1].' ) ))))';
		}*/

		$where_variant="";
		$distinct="";
		if(!empty($variant_id_arr) && !empty($variant_attr_value_arr)) {
			$str_id = "'".implode("','",$variant_id_arr)."'";
			$str_value = "'".implode("','",$variant_attr_value_arr)."'";

			$where_variant = 'INNER JOIN '.$shop_db.'.products_variants as prv ON prv.parent_id='.$shop_db.'.products_category.product_id AND prv.attr_id IN ('.$str_id.') AND prv.attr_value IN ('.$str_value.')';
			$distinct = 'DISTINCT prv.parent_id,';
		}

		$where_attribute="";
		$where_attr='';
		if(!empty($attribute_arr)) {
			/*
			$str_attrid = "'".implode("','",$attribute_arr)."'";
			$where_attribute = 'INNER JOIN '.$shop_db.'.products_attributes as p_attr ON p_attr.product_id='.$shop_db.'.products_category.product_id AND p_attr.attr_id IN ('.$str_attrid.')';

			*/

			$wg_case=array();
			$where_attr .=  ' AND ( ';
			foreach($attribute_arr as $key=>$val){
			$wg_case[]="FIND_IN_SET('".$val."',p_attr.attr_value)";
			}
			$where_attr .=  implode(' OR ',$wg_case);
			$where_attr .=  ' )';

			$where_attribute = 'INNER JOIN '.$shop_db.'.products_attributes as p_attr ON p_attr.product_id='.$shop_db.'.products_category.product_id  '.$where_attr;
			$distinct = 'DISTINCT p_attr.product_id,';
		}

		$where_search="";
		if($search_term!='') {
			//$where_search = 'prod.name LIKE "%'.$search_term.'%" AND';
			$where_search = '(prod.name LIKE "%'.$search_term.'%" OR prod.product_code LIKE "%'.$search_term.'%" OR prod.highlights LIKE "%'.$search_term.'%" OR prod.search_keywords LIKE "%'.$search_term.'%" OR prod.promo_reference LIKE "%'.$search_term.'%") AND';
		}

		$popular_count = '';
		$popular = '';
  		if($options=='price_des'){
  			$order_by = ' ORDER BY prod.webshop_price DESC';
  		}else if($options=='price_asc'){
  			$order_by = ' ORDER BY prod.webshop_price ASC';
  		}else if($options=='popular'){
  			//$popular_count = ',COUNT("s.*") as popular';
  			//$popular = 'LEFT JOIN '.$shop_db.'.sales_order_items as s ON ((s.product_id = prod.id AND prod.product_type="simple") OR (s.parent_product_id = prod.id AND prod.product_type="configurable"))';
  			//$order_by = ' ORDER BY popular DESC';
			$order_by = ' ORDER BY prod.launch_date DESC, prod.id DESC ';
  		}else{
  			$order_by = ' ORDER BY prod.launch_date DESC, prod.id DESC ';
  		}

		$limit=" ";


  		$date = strtotime(date('d-m-Y'));
		//echo $date;
  		$param = array($date,1,0);
		//echo date('d-m-Y');
		// $sql = "SELECT ".$distinct." prod.*".$popular_count." FROM ".$shop_db.".products as prod INNER JOIN ".$shop_db.".products_category ON prod.id=".$shop_db.".products_category.product_id AND FIND_IN_SET(".$categoryid.",category_ids) ".$where_variant.$where_attribute.$popular." WHERE prod.launch_date <= ".$date." AND ((FIND_IN_SET(".$customer_type_id.",customer_type_ids)) OR (customer_type_ids='0')) AND ((prod.product_type='simple' ) OR (prod.product_type='configurable' )) ".$where_gender.$where_price ."AND prod.status='1' AND prod.remove_flag='0' GROUP BY prod.id".$order_by;
		// echo $sql;exit;
		$sub_query='';
		if($customer_type_id!='' && $customer_type_id > 2)
		{
			$sub_query = " OR (prod.customer_type_ids='2')";
		}

  		if($categoryid!=''){
  			$query = "SELECT prv.product_id as child_product_id,prod.shop_id as child_shop_id,prod.parent_id as child_parent_id FROM $shop_db.products as prod INNER JOIN $shop_db.products_category ON prod.id=$shop_db.products_category.product_id AND FIND_IN_SET($categoryid,products_category.category_ids) $where_variant $where_attribute $popular WHERE prod.launch_date <= ? AND ((FIND_IN_SET($customer_type_id,prod.customer_type_ids)) OR (prod.customer_type_ids='0') $sub_query) AND (prod.product_type IN ('simple','configurable')) $where_gender $where_price AND prod.status=? AND prod.remove_flag=? GROUP BY prod.id $order_by  ";
  		}else{
			$query = "SELECT prv.product_id as child_product_id,prod.shop_id as child_shop_id,prod.parent_id as child_parent_id as prod $popular WHERE $where_search prod.launch_date <= ? AND ((FIND_IN_SET($customer_type_id,prod.customer_type_ids)) OR (prod.customer_type_ids='0') $sub_query) AND (prod.product_type IN ('simple','configurable')) AND prod.status=? AND prod.remove_flag=? GROUP BY prod.id $order_by   ";
  		}

		// echo $query;exit;

		$product_list = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $product_list;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	public function getInventory($product_id)
  	{

  		$param = array($product_id);
		$query = "SELECT products_inventory.* FROM products_inventory WHERE products_inventory.product_id = ?";
		$inventory = $this->dbl->dbl_conn->rawQueryOne($query,$param);
		// print_r($inventory);die();
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

  	public function getParent_id($product_id, $shopcode)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id);
		$query = "SELECT $shop_db.products_variants.parent_id FROM $shop_db.products_variants WHERE $shop_db.products_variants.product_id = ?";
		// print_r($query);die();
		$parent_id = $this->dbl->dbl_conn->rawQueryOne($query,$param);
		// print_r($inventory);die();
  		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $parent_id;
			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

  	public function mainCategory($shopcode,$shopid,$customer_type_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$date = strtotime(date('d-m-Y'));

  		$param = array($date,0,0,1,$shopid);

  		$sub_query='';
		if($customer_type_id > 2 )
		{
			$sub_query = "OR (prod.customer_type_ids='2')";
		}

		$query = "SELECT b2b.category_id, main_cat.*, COUNT(prod.id) as product_count FROM $shop_db.fbc_users_category_b2b as b2b INNER JOIN $main_db.category as main_cat ON main_cat.id = b2b.category_id INNER JOIN $shop_db.products_category as pc ON pc.category_ids = b2b.category_id LEFT JOIN $shop_db.products as prod ON prod.id = pc.product_id AND prod.launch_date <= ? AND ((FIND_IN_SET($customer_type_id,prod.customer_type_ids)) OR (prod.customer_type_ids='0') $sub_query) AND prod.remove_flag=? WHERE b2b.level = ? AND main_cat.status = ? AND ((main_cat.shop_id = ?) OR (main_cat.created_by_type = '0'))  AND prod.status=1  GROUP BY pc.category_ids ORDER BY main_cat.id ASC";
		// print_r($query);exit;
  		$main_category = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $main_category;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function firstLevelCategory($shopcode,$shopid,$cat_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array(1,1,$cat_id,$shopid);

		$query = "SELECT b2b.category_id, main_cat.* FROM $shop_db.fbc_users_category_b2b as b2b INNER JOIN $main_db.category as main_cat ON main_cat.id = b2b.category_id WHERE b2b.level = ? AND main_cat.status = ? AND main_cat.parent_id = ? AND ((main_cat.shop_id = ?) OR (main_cat.created_by_type = '0')) ";

  		$level1Cat = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $level1Cat;
			}else{
				return false;
			}
		}

	}

	public function secondLevelCategory($shopcode,$shopid,$cat_parent_id,$cat_main_parent_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array(2,1,$cat_parent_id,$cat_main_parent_id,$shopid);

		$query = "SELECT b2b.category_id, main_cat.* FROM $shop_db.fbc_users_category_b2b as b2b INNER JOIN $main_db.category as main_cat ON main_cat.id = b2b.category_id WHERE b2b.level = ? AND main_cat.status = ? AND main_cat.parent_id = ? AND main_cat.main_parent_id = ? AND ((main_cat.shop_id = ?) OR (main_cat.created_by_type = '0'))";

  		$level2Cat = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $level2Cat;
			}else{
				return false;
			}
		}

	}

	public function productDetails($product_url_key)
  	{

  		$param = array($product_url_key, 0);
		//   "SELECT p.*, pub.publication_name FROM products as p Inner JOIN publisher as pub ON p.publisher_id=pub.id WHERE p.url_key = 'bike-india-magazine-0' AND p.remove_flag= 0 AND p.status= 1"

		// $query = "SELECT p.*  FROM products as p  WHERE p.url_key = ? AND p.remove_flag= ? AND p.status= 1";
		$query = "SELECT p.*, pub.publication_name FROM products as p 
		INNER JOIN publisher as pub ON p.publisher_id=pub.id  
		WHERE p.url_key =? AND p.remove_flag= ? AND p.status= 1";
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

  	public function configurableProduct($product_id)
  	{
  		$param = array($product_id);
		$query = "SELECT products.* FROM products WHERE products.parent_id = ? ";

  		$config_product = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $config_product;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}


	public function configurableProductForMultipleProducts($product_ids)
	{
		$product_ids_string = implode(',',$product_ids);
		$query = "SELECT products.* FROM products WHERE products.parent_id IN ($product_ids_string) ";

		$config_products = $this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $config_products;
			}
		}

		return [];
	}


	public function configProductVariant($product_id)
  	{
  		$param = array($product_id);
		$query = "SELECT eav_attr.id, eav_attr.attr_code, eav_attr.attr_name FROM products_variants_master as pv_m INNER JOIN eav_attributes as eav_attr ON eav_attr.id=pv_m.attr_id WHERE pv_m.product_id=? ORDER BY position ASC";

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

  	public function variantOptions($shopcode,$shopid,$product_id,$variant_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id,$variant_id);

		$query = "SELECT DISTINCT pv.attr_value, eav_attr_opt.attr_options_name FROM $shop_db.products_variants as pv INNER JOIN $main_db.eav_attributes_options as eav_attr_opt ON eav_attr_opt.id = pv.attr_value WHERE pv.parent_id=? AND pv.attr_id=? ";

		$variant_option = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $variant_option;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	// product variant instock
  	public function productVariantOptionsInstock($shopcode,$shopid,$product_id,$variant_id,$childProductsNotStock)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id,$variant_id,$childProductsNotStock);

		$query = "SELECT DISTINCT pv.attr_value, eav_attr_opt.attr_options_name FROM $shop_db.products_variants as pv INNER JOIN $main_db.eav_attributes_options as eav_attr_opt ON eav_attr_opt.id = pv.attr_value WHERE pv.parent_id=? AND pv.attr_id=? AND pv.product_id not in (?)";

		$variant_option = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $variant_option;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	public function productVariantOptionsInstockNewQuery($product_id,$variant_id,$childProductsNotStock)
  	{
	  
  		$param = array($product_id,$variant_id);
		$query = "SELECT DISTINCT pv.attr_value, eav_attr_opt.attr_options_name, pv.product_id, prod.gift_id, prod.sub_issues, prod.webshop_price, gift.name as gift_master_name FROM products_variants as pv INNER JOIN eav_attributes_options as eav_attr_opt ON eav_attr_opt.id = pv.attr_value INNER JOIN products as prod On pv.product_id = prod.id 
		INNER JOIN gift_master as gift On prod.gift_id = gift.id WHERE pv.parent_id=? AND pv.attr_id=? ";
		if($childProductsNotStock ){
			$query .= " AND pv.product_id NOT IN ($childProductsNotStock)";
  		}
		$query .=" ORDER BY eav_attr_opt.position ASC";
		//$variant_option = $this->dbl->dbl_conn->query($query);
		$variant_option = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $variant_option;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	public function getMinPrice($product_id)
  	{
  		$param = array($product_id);
  		$query = "SELECT MIN(webshop_price) as min_price, MAX(webshop_price) as max_price FROM products WHERE products.parent_id = ? ";

  		$minPrice = $this->dbl->dbl_conn->rawQueryOne($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $minPrice;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	public function attributDetails($product_id,$lang_code='')
  	{

  		$param = array($product_id,1);
		 $query = "SELECT prd_attr.attr_value, eav_attr.* FROM products_attributes as prd_attr 
		  INNER JOIN eav_attributes as eav_attr ON eav_attr.id = prd_attr.attr_id WHERE prd_attr.product_id = ?  AND eav_attr.status = ? AND eav_attr.attr_properties NOT IN (5,6) ";

  		$attribut_data = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $attribut_data;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	public function mediaGallery($product_id)
  	{
  		$param = array($product_id);

  		$query = "SELECT m_gallery.* FROM products_media_gallery as m_gallery WHERE m_gallery.product_id = ?  order by m_gallery.is_base_image,m_gallery.image_position ASC";

  		$galleryImages = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $galleryImages;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	public function getmediaGalleryByVariants($shopcode,$shopid,$product_id,$child_prod_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id,$child_prod_id);

  		$query = "SELECT m_gallery.* FROM $shop_db.products_media_gallery as m_gallery WHERE m_gallery.product_id = ? AND m_gallery.child_id = ? order by  m_gallery.is_base_image,m_gallery.image_position  ASC";

  		$variantsgalleryImages = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $variantsgalleryImages;
			}else{
				$param1 = array($product_id);
				$query1 = "SELECT m_gallery.* FROM $shop_db.products_media_gallery as m_gallery WHERE m_gallery.product_id = ? AND m_gallery.child_id IS NULL  order by m_gallery.is_base_image,m_gallery.image_position ASC";

				$galleryImagesVariants = $this->dbl->dbl_conn->rawQuery($query1,$param1);
				if ($this->dbl->dbl_conn->getLastErrno() === 0){
					if ($this->dbl->dbl_conn->count > 0){
						return $galleryImagesVariants;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}
		}else{
			return false;
		}

  	}

	public function getproductDetailsById($product_id)
  	{
		return  $this->getproductDetailsByShopCode($product_id);
  	}

	public function getproductDetailsByShopCode($product_id)
  	{
  		$param = [$product_id];
		$query = "SELECT products.* FROM products WHERE products.id = ? ";

  		$product_detail = $this->dbl->dbl_conn->rawQueryOne($query,$param);

		if (($this->dbl->dbl_conn->getLastErrno() === 0) && $this->dbl->dbl_conn->count > 0) {
			return $product_detail;
		}

		return false;
	}

	public function getMultipleProductDetails($product_ids)
  	{
  		$query = "SELECT products.* FROM products WHERE products.id IN (" . implode(',', $product_ids) . ")";
  		$products = $this->dbl->dbl_conn->rawQuery($query);

        $result = [];
        foreach($products as $product){
            $result[$product['id']] = $product;
        }

        return $result;
	}

	public function productVariantMaster($shopcode,$product_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id);
		$query = "SELECT pvm.*,eav_attr.attr_name,eav_attr.attr_code FROM $shop_db.products_variants_master as pvm INNER JOIN $main_db.eav_attributes as eav_attr ON eav_attr.id = pvm.attr_id WHERE pvm.product_id = ? AND eav_attr.attr_type = 2 ";

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

	public function getCategoryDetailsById($category_id)
  	{
  		//$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($category_id);
		$query = "SELECT $main_db.category.* FROM $main_db.category WHERE id = ? ";

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

	public function getProductCategoryByLevel($product_id,$level='')
  	{

  		$param = array($product_id,$level);

		$query = "SELECT pc.*,c.slug,c.cat_name FROM products_category as pc INNER JOIN category as c ON c.id = pc.category_ids WHERE pc.product_id = ?  AND  pc.level = ? ";
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

  	public function getProductCategorys($product_id)
  	{
  		$param = array($product_id);

		$query = "SELECT GROUP_CONCAT(DISTINCT category_ids SEPARATOR ',') AS cat_ids  FROM products_category as pc INNER JOIN category as c ON c.id = pc.category_ids WHERE c.status = 1 and pc.product_id = ?  ";
  		$cat_id_arr = $this->dbl->dbl_conn->rawQueryOne($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $cat_id_arr;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

	public function getsimplevariantproducts($parent_id,$attr_id,$attr_value,$product_id='')
  	{


		if(isset($product_id) && $product_id>0){
			$param = array($parent_id,$attr_id,$attr_value,$product_id);
			$query = "SELECT pv.* FROM products_variants as pv WHERE pv.parent_id = ? AND pv.attr_id = ? AND pv.attr_value = ? AND pv.product_id = ? ";

			$product_variant = $this->dbl->dbl_conn->rawQueryOne($query,$param);

		}else{
			$param = array($parent_id,$attr_id,$attr_value);
			$query = "SELECT pv.* FROM products_variants as pv WHERE pv.parent_id = ? AND pv.attr_id = ? AND pv.attr_value = ? ";

			$product_variant = $this->dbl->dbl_conn->rawQuery($query,$param);
		}

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $product_variant;
			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

	public function checksimplevariantproductcount($shopcode,$parent_id,$attr_id,$attr_value,$product_id='')
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		if(isset($product_id) && $product_id>0){
			$param = array($parent_id,$attr_id,$attr_value,$product_id);
			$query = "SELECT count(*) as count FROM $shop_db.products_variants as pv WHERE pv.parent_id = ? AND pv.attr_id = ? AND pv.attr_value = ? AND pv.product_id = ? ";

			$product_variant = $this->dbl->dbl_conn->rawQueryOne($query,$param);

		}else{
			$param = array($parent_id,$attr_id,$attr_value);
			$query = "SELECT count(*) as count FROM $shop_db.products_variants as pv WHERE pv.parent_id = ? AND pv.attr_id = ? AND pv.attr_value = ? ";

			$product_variant = $this->dbl->dbl_conn->rawQueryOne($query,$param);
		}

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $product_variant;
			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

	public function simplevariantproductexistbyattrid($parent_id,$attr_id,$attr_value,$product_id)
  	{

		$param = array($parent_id,$attr_id,$attr_value,$product_id);
			$query = "SELECT count(*) as count FROM products_variants as pv WHERE pv.parent_id = ? AND pv.attr_id = ? AND attr_value = ? AND pv.product_id = ? ";

			$product_variant = $this->dbl->dbl_conn->rawQueryOne($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $product_variant;
			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

	public function attributListingForCatalog($shopcode,$shopid,$product_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id,1,1,1); //5 - dropdown, 6 - multiselect

  		$sql = "SELECT prd_attr.attr_value, eav_attr.* FROM $shop_db.products_attributes as prd_attr INNER JOIN $shop_db.fbc_users_attributes_visibility as fbc_attr_visb ON fbc_attr_visb.attr_id = prd_attr.attr_id INNER JOIN $main_db.eav_attributes as eav_attr ON eav_attr.id = fbc_attr_visb.attr_id WHERE prd_attr.product_id = ? AND fbc_attr_visb.filterable_with_results = ? AND eav_attr.status = ? AND eav_attr.attr_type = ? AND (eav_attr.attr_properties IN (5,6))";

  		$attributdata = $this->dbl->dbl_conn->rawQuery($sql,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $attributdata;

			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

	  public function getAvailableInventoryForMultipleProducts($product_ids) {
		  $product_ids_string = implode(',',$product_ids);
		  $query = "SELECT products_inventory.* FROM products_inventory WHERE products_inventory.product_id IN ($product_ids_string)";
		  $inventory = $this->dbl->dbl_conn->rawQuery($query);
		  if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				// $temp = $inventory;
				return $temp;  // return result
			}else{
				return false;
			}
		}else{
			return false;
		}
	  }

	  public function getAvailableInventory($product_id, $Product = null, $inventory = null)
  	{
		
  		if($inventory === null) {
			//echo 11;die();
			$param = array($product_id);
			$query = "SELECT products_inventory.* FROM products_inventory WHERE products_inventory.product_id = ?";
			$inventory = $this->dbl->dbl_conn->rawQueryOne($query,$param);
		}



  		if (is_array($inventory) && count($inventory) > 0) {
			  if($Product === null){
				  $Product = $this->getproductDetailsByShopCode($product_id);
			  }

			  if($inventory['available_qty']<=0){
				  if($Product['id']!= ''){
					  $query1 = "SELECT products_inventory.* FROM products_inventory WHERE products_inventory.product_id = ?";

					  $inventory1 = $this->dbl->dbl_conn->rawQueryOne($query1,$new_param);

					  if ($this->dbl->dbl_conn->getLastErrno() === 0){
						  if ($this->dbl->dbl_conn->count > 0){
							  return $inventory1;  // return result
						  }else{
							  return false;
						  }
					  }else{
						  return false;
					  }
				  }else{
					  return false;
				  }

			  }else{

				  return $inventory; // return result
			  }
		  } else{
			return false;
		}

  	}

	public function checkInventorySource($product_id, $shopcode, $seller_shopcode='')
  	{
		$Result=array();
		$source='';
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id);
		$query = "SELECT $shop_db.products_inventory.* FROM $shop_db.products_inventory WHERE $shop_db.products_inventory.product_id = ?";
		$inventory = $this->dbl->dbl_conn->rawQueryOne($query,$param);

  		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){

				$Product=$this->getproductDetailsByShopCode($shopcode,$product_id);
				$product_inv_type=$Product['product_inv_type'];

				if(($product_inv_type=='dropship')  || ($product_inv_type=='virtual' && $inventory['qty']<=0)){
					$shop_product_id=$Product['shop_product_id'];
					if($shop_product_id>0){
						$seller_db =  DB_NAME_SHOP_PRE.$seller_shopcode;
						$new_param=array($shop_product_id);  //updated by al

						$query1 = "SELECT $seller_db.products_inventory.* FROM $seller_db.products_inventory WHERE $seller_db.products_inventory.product_id = ?";

						$inventory1 = $this->dbl->dbl_conn->rawQueryOne($query1,$new_param);

						if ($this->dbl->dbl_conn->getLastErrno() === 0){
							if ($this->dbl->dbl_conn->count > 0){
								$Result['inventory']=$inventory1;
								$Result['db']=$seller_shopcode;
								return $Result;  // return result
							}else{
								return false;
							}
						}else{
							return false;
						}
					}else{
						return false;
					}

				}else{
					$Result['inventory']=$inventory;   // updated apr 2021
					$Result['db']=$shopcode;
					return $Result; // return result
				}
			}else{
				return false;
			}

		}else{
			return false;
		}

  	}

  	public function getEstimateDeliveryTime($shopcode, $seller_shopid, $shopid='')
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.'shop'.$seller_shopid; //Constant variable
  		$main_db = DB_NAME; //Constant variable

		if($shopid == '') {
			$shopid = str_replace("shop", "", $shopcode);
		}

  		$param = array($shopid);
		//$query = "SELECT fbd.* FROM $main_db.fbc_users_b2b_details as fbd WHERE fbd.shop_id = ?";
		$query = "SELECT t2.* FROM $shop_db.b2b_customers t1, $shop_db.b2b_customers_details t2 where t1.shop_id = ? and t1.id = t2.customer_id";
		$delivery_time = $this->dbl->dbl_conn->rawQueryOne($query,$param);

  		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $delivery_time;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	public function getInventoryForVertual($product_id, $shopcode)
  	{
  		$shop_db = DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id);
		$query = "SELECT inv.* FROM $shop_db.products_inventory as inv WHERE inv.product_id = ?";
		$inventory = $this->dbl->dbl_conn->rawQueryOne($query,$param);

  		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $inventory;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function getSpecialPrices($product_id)
  	{
  		$date = strtotime(date('d-m-Y'));

  		$param = array($product_id,$date,$date);
		$query = "SELECT psp.* FROM products_special_prices as psp WHERE psp.product_id = ?  AND ( psp.special_price_from <= ? AND psp.special_price_to >= ? )";
		$products = $this->dbl->dbl_conn->rawQueryOne($query,$param);
  					
		if ($this->dbl->dbl_conn->getLastErrno() === 0){


			if ($this->dbl->dbl_conn->count > 0){
				return $products;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}


	public function getSpecialPricesForMultipleProducts(array $product_ids)
	{
		$date = strtotime(date('Y-m-d'));
		$product_ids_string = implode(',',$product_ids);

		$query = "SELECT psp.* FROM products_special_prices as psp WHERE psp.product_id IN ($product_ids_string) AND ( psp.special_price_from <= ? AND psp.special_price_to >= ? )";

		$param = [
			$date,
			$date
		];
		$products = $this->dbl->dbl_conn->rawQuery($query,$param);

		if (($this->dbl->dbl_conn->getLastErrno() === 0) && $this->dbl->dbl_conn->count > 0) {
			return $products;
		}

		return [];
	}


	 public function getFiltersVariantMaster($categoryid,$lang_code='')
  	{
		if($categoryid !=''){
				$query = "SELECT distinct(eav_attr.id) as id, eav_attr.attr_code, eav_attr.attr_name FROM products_variants_master as pv_m

  		 		INNER JOIN eav_attributes as eav_attr ON eav_attr.id=pv_m.attr_id
				INNER JOIN products_category as pc ON pc.product_id=pv_m.product_id
				where pc.category_ids=$categoryid ORDER BY eav_attr.id ASC";
			}
			else{
				
				$query = "SELECT distinct(eav_attr.id) as id, eav_attr.attr_code, eav_attr.attr_name FROM products_variants_master as pv_m
  		 		INNER JOIN eav_attributes as eav_attr ON eav_attr.id=pv_m.attr_id
				ORDER BY eav_attr.id ASC";
			}
  		
  		$variant_prod = $this->dbl->dbl_conn->rawQuery($query);
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

	public function getFiltersVariantOptions($shopcode,$shopid,$variant_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($variant_id);

		$query = "SELECT DISTINCT pv.attr_value, eav_attr_opt.attr_options_name FROM $shop_db.products_variants as pv INNER JOIN $main_db.eav_attributes_options as eav_attr_opt ON eav_attr_opt.id = pv.attr_value WHERE  pv.attr_id=?";

		$variant_option = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $variant_option;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}



	public function getOptionsByVariantId($shopcode,$shopid,$variant_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($variant_id);


		$query = "SELECT id,attr_id,attr_options_name FROM  $main_db.eav_attributes_options  WHERE  attr_id=?  AND  status=1 AND(shop_id=$shopid OR created_by_type=0)";

		$variant_option = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $variant_option;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

	public function getOptionsByVariantIdForMultiple($attr_ids)
  	{
  		$attr_ids_string = implode(',',$attr_ids);
		$query = "SELECT id,attr_id,attr_options_name FROM  eav_attributes_options  WHERE attr_id IN ($attr_ids_string)  AND  status=1 ORDER BY eav_attributes_options.position ASC";

		$variant_option = $this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $variant_option;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

	public function checkProductCountByVariantOption($attr_id,$variant_value_ids,$categoryid)
  	{
  		$date = strtotime(date('d-m-Y'));

  		$param = array($attr_id,$categoryid,$date);
			$query = "SELECT DISTINCT(pv.attr_value) as attr_value, COUNT(*) as count FROM products_variants as pv
			INNER JOIN products_category as pc ON pc.product_id=pv.parent_id
			INNER JOIN  products as p ON p.id = pv.parent_id
			WHERE  pv.attr_id = ? AND pv.attr_value  IN (" . implode(',', $variant_value_ids) . ")  AND pc.category_ids=?  AND p.status=1 AND p.remove_flag=0  AND p.launch_date <= ? GROUP BY pv.attr_value HAVING `count` > 0";

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

	public function checkProductCountByVariantMultiple($attr_id,$option_ids,$categoryid)
  	{
  		$date = strtotime(date('d-m-Y'));

  		$param = array($categoryid,$date);
  		$option_ids_string = implode(',',$option_ids);

		$query = "SELECT pv.attr_value FROM products_variants as pv
		INNER JOIN products_category as pc ON pc.product_id=pv.parent_id  
		INNER JOIN  products as p ON p.id = pv.parent_id
		WHERE  pv.attr_id=$attr_id AND pv.attr_value IN ($option_ids_string) AND pc.category_ids=?  AND p.status=1 AND p.remove_flag=0  AND p.launch_date <= ?";

		$Row = $this->dbl->dbl_conn->rawQuery($query,$param);

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

	 public function getFiltersAttributeMasterOfShop($lang_code='')
  	{
  	
		$query = "SELECT eav_attr.* FROM eav_attributes as eav_attr 
		where eav_attr.status=1 AND eav_attr.attr_type = 1 AND (eav_attr.attr_properties IN (5,6)) ORDER BY eav_attr.id ASC";

  		$variant_prod = $this->dbl->dbl_conn->rawQuery($query);

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

	  public function checkProductCountByAttributeOption($attr_id,$option_id,$categoryid)
  	{
  		$date = strtotime(date('d-m-Y'));

  		$param = array($attr_id,$categoryid,$date);

		$query = "SELECT count(*) as count FROM products_attributes as prd_attr
		INNER JOIN products_category as pc ON pc.product_id=prd_attr.product_id
		INNER JOIN  products as p ON p.id = prd_attr.product_id		
		WHERE  prd_attr.attr_id=? AND pc.category_ids=? AND p.status=1 AND p.remove_flag=0  AND p.launch_date <= ? AND (FIND_IN_SET($option_id,prd_attr.attr_value))";

		$Row = $this->dbl->dbl_conn->rawQueryOne($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $Row['count'];
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

	public function checkProductCountByAttributeMultiple($attr_id,$option_ids,$categoryid)
  	  	{
  	  		$date = strtotime(date('d-m-Y'));

  	  		$param = array($attr_id,$categoryid,$date);
  	  		$option_ids_string = implode(',',$option_ids);

  			$query = "SELECT prd_attr.attr_value FROM products_attributes as prd_attr
  			INNER JOIN products_category as pc ON pc.product_id=prd_attr.product_id
  			INNER JOIN  products as p ON p.id = prd_attr.product_id		
  			WHERE  prd_attr.attr_id =? AND pc.category_ids=? AND p.status=1 AND p.remove_flag=0  AND p.launch_date <= ? AND prd_attr.attr_value IN($option_ids_string)";

  			$Row = $this->dbl->dbl_conn->rawQuery($query,$param);

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

	public function getSingleEAVOptionDetails($option_id)
  	{
		$query = "SELECT GROUP_CONCAT(attr_options_name SEPARATOR ', ') as attr_options_name FROM eav_attributes_options as fbd WHERE fbd.id  IN ( $option_id ) ";
		$delivery_time = $this->dbl->dbl_conn->rawQueryOne($query);

  		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $delivery_time;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	public function attributeDropdownForProductDetail($product_id,$lang_code='')
  	{

  		$param = array($product_id,1,1); //5 - dropdown, 6 - multiselect

		  
		  $sql = "SELECT prd_attr.attr_value, eav_attr.* FROM products_attributes as prd_attr 
  		INNER JOIN eav_attributes as eav_attr ON eav_attr.id = prd_attr.attr_id
  		 WHERE prd_attr.product_id = ?  AND eav_attr.status = ? AND eav_attr.attr_type = ? AND (eav_attr.attr_properties IN (5,6))";
  		
  		$attributdata = $this->dbl->dbl_conn->rawQuery($sql,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				$final_result=array();
				foreach($attributdata as $key=>$val){
					if(isset($val['multi_attr_name']) && $val['multi_attr_name']!=''){
					$attr_name=$val['multi_attr_name'];	
					}else{
					$attr_name=$val['attr_name'];	
					}
					$OptionsName=$this->getSingleEAVOptionDetails($val['attr_value']);
					if($OptionsName!=false){
						$attr_options_name=$OptionsName['attr_options_name'];
						$row_arr['attr_name']=$attr_name;
						$row_arr['attr_value']=$attr_options_name;
						$final_result[]=$row_arr;
					}
				}

				return $final_result;
			}else{
				return false;
			}
		}else{
			return false;
		}
  	}


	public function CheckProductsAvailable($barcode,$sku)
	{
		if($barcode !='' || $sku !='')
		{
		 	$where_barcode = '';
            if($barcode !=''){
            	$where_barcode = "AND p.barcode ='".$barcode."'";
            }
            $where_sku= '';
            if($sku !=''){
				$where_sku = "AND p.sku ='".$sku."'";
            }

            $query = "SELECT p.* FROM products as p WHERE ((p.product_type = 'simple') OR (p.product_type = 'conf-simple')) AND p.remove_flag = 0 $where_barcode $where_sku ";

            $product = $this->dbl->dbl_conn->rawQueryOne($query);
			if ($this->dbl->dbl_conn->getLastErrno() === 0)
			{
				if ($this->dbl->dbl_conn->count > 0){
					return $product;
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

	public function getProductSku($term)
	{

		if($term !='')
		{
		 	$where_term = '';
            if($term !=''){
            	$where_term = "AND name like '%".$term."%' or sku like '%".$term."%'";
            }


            $query = "SELECT p.* FROM products as p WHERE ((p.product_type = 'simple') OR (p.product_type = 'conf-simple')) AND p.remove_flag = 0  $where_term ";

            $product = $this->dbl->dbl_conn->rawQuery($query);
            // return $this->dbl->dbl_conn->getLastQuery();
			if ($this->dbl->dbl_conn->getLastErrno() === 0)
			{
				if ($this->dbl->dbl_conn->count > 0){
					return $product;
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

	public function insert_productdata($shopcode,$insert_productdata_arr)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		// $table_name  = "'". $shop_db.".`customers`'" ;
		$columns = implode(", ",array_keys($insert_productdata_arr));
		$escaped_values =  array_values($insert_productdata_arr);
		$values  = implode(", ", $escaped_values);
		$query = "insert into $shop_db.catlog_builder_scanning ($columns) VALUES ($values) ";
		$this->dbl->dbl_conn->rawQueryOne($query);

		if($query)
		{
			return true;
		}else{
			return false;
		}
	}

	 public function getScannedProductsData($shopcode,$customer_id)
	 {
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		// $offset = (isset($offset) && $offset != Null &&  $offset != '') ? $offset :  0;

		$sql =  "SELECT * FROM $shop_db.catlog_builder_scanning where `customer_id` = '$customer_id'  order by created_at DESC ";
		$row  = $this->dbl->dbl_conn->rawQuery($sql);

		// echo $this->dbl->dbl_conn->getLastQuery();
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

	 public function countgetScannedProductsData($shopcode,$customer_id)
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable


		 $sql =  "SELECT * FROM $shop_db.catlog_builder_scanning where `customer_id` = '$customer_id'  order by created_at DESC ";
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

	  public function insert_catlog_Builder_data($shopcode,$insert_catlog_Builder_arr)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		// $table_name  = "'". $shop_db.".`customers`'" ;
		$columns = implode(", ",array_keys($insert_catlog_Builder_arr));
		$escaped_values =  array_values($insert_catlog_Builder_arr);
		$values  = implode(", ", $escaped_values);
		$query = "insert into $shop_db.catalog_builder ($columns) VALUES ($values) ";
		$this->dbl->dbl_conn->rawQueryOne($query);
		$last_insert_id = $this->dbl->dbl_conn->getInsertId();
		if($query)
		{
			return $last_insert_id;
		}else{
			return false;
		}
	}

	public function insert_catlog_Builder_items_data($shopcode,$insert_catlog_Builder_items_arr)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		// $table_name  = "'". $shop_db.".`customers`'" ;
		$columns = implode(", ",array_keys($insert_catlog_Builder_items_arr));
		$escaped_values =  array_values($insert_catlog_Builder_items_arr);
		$values  = implode(", ", $escaped_values);
		$query = "insert into $shop_db.catalog_builder_items ($columns) VALUES ($values) ";
		$this->dbl->dbl_conn->rawQueryOne($query);
		$last_insert_id = $this->dbl->dbl_conn->getInsertId();
		if($query)
		{
			return $last_insert_id;
		}else{
			return false;
		}
	}

	public function deleteScannedProduct($shopcode,$id){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		//$param = array($loginID,$quote_id);
		$query = "Delete FROM $shop_db.catlog_builder_scanning WHERE id=$id";


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

	public function deleteAllScannedProduct($shopcode,$customer_id){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		//$param = array($loginID,$quote_id);
		$query = "Delete FROM $shop_db.catlog_builder_scanning WHERE customer_id=$customer_id";


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

	/*wishlist count*/
	public function getProductCutsomerExistWishlistCount($shopcode,$customer_id,$prod_id)
  	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME;
  		$Row = "SELECT count(*) as count FROM $shop_db.wishlist_items WHERE customer_id = '$customer_id' AND product_id = '$prod_id'";
		$query  = $this->dbl->dbl_conn->rawQueryOne($Row);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if($this->dbl->dbl_conn->count > 0){
				return $query['count'];
			}else{
				return false;
			}
		}else{
			return false;
		}
  	}


	public function getWishlistCountForMultipleProducts($customer_id, $product_ids): array
	{
		if(count($product_ids) === 0) {
			return [];
		}

		$product_ids_string = implode(',',$product_ids);

		$query = "SELECT product_id FROM wishlist_items WHERE customer_id = $customer_id AND product_id IN ($product_ids_string)";

		$result  = $this->dbl->dbl_conn->rawQuery($query);
		if (($this->dbl->dbl_conn->getLastErrno() === 0) && $this->dbl->dbl_conn->count > 0) {
			return array_column($result, 'product_id');
		}
		return [];
	}

  	/*wishlist count*/



  	public function getBaseColorDatatest($shopcode, $shopid): array
	{
	
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME;

		// $query = "SELECT bc.*, bcv.variant_option_id as variant_id  FROM $shop_db.base_colors as bc LEFT JOIN $shop_db.base_colors_variants as bcv ON bc.id=bcv.base_colur_id  WHERE bc.status=1";
		$query = "SELECT bc.*  FROM $shop_db.base_colors as bc WHERE bc.status=1";
		$result  = $this->dbl->dbl_conn->rawQuery($query);
		if (($this->dbl->dbl_conn->getLastErrno() === 0) && $this->dbl->dbl_conn->count > 0) {
			// $array
			/*foreach($result as $key=>$val){
				//print_r($val['id']);
				// echo $val['variant_id'];
				$query1 = "SELECT bcv.*  FROM $shop_db.base_colors_variants as bcv WHERE bcv.base_colur_id=".$val['id']. " order by bcv.base_colur_id";
				$result1  = $this->dbl->dbl_conn->rawQuery($query1);
				foreach ($result1 as $key => $value) {
					if($value['base_colur_id']==$val['id']){

					}
					echo $value['variant_option_id'];
					print_r($value);
				}
				// print_r($result1);
			}*/

			 return $result;
		}else{
			return false;
		}

	}


	public function getBaseColorData ($shopcode, $shopid):array{


		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME;

		// $query = "SELECT bc.*, bcv.variant_option_id as variant_id  FROM $shop_db.base_colors as bc LEFT JOIN $shop_db.base_colors_variants as bcv ON bc.id=bcv.base_colur_id  WHERE bc.status=1";
		$query = "SELECT bc.*  FROM $shop_db.base_colors as bc WHERE bc.status=1";
		$result  = $this->dbl->dbl_conn->rawQuery($query);
		if (($this->dbl->dbl_conn->getLastErrno() === 0) && $this->dbl->dbl_conn->count > 0) {
			 return $result;
		}else{
			return false;
		}
	}

	public function getOptionsByBaseColorIdForMultiple($shopcode,$shopid,$base_ids)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$attr_ids_string = implode(',',$base_ids);
		$query = "SELECT * FROM  $shop_db.base_colors_variants  WHERE base_color_id IN ($attr_ids_string)";

		$variant_option = $this->dbl->dbl_conn->rawQuery($query);


		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $variant_option;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	

  	public function checkProductCountByVariantMultipleBaseColor($shopcode,$shopid,$attr_id,$option_ids,$categoryid,$customer_type_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$date = strtotime(date('d-m-Y'));

  		$param = array($categoryid,$date);
  		$option_ids_string = implode(',',$option_ids);
		$sub_query='';
		if($customer_type_id > 2 )
		{
			$sub_query = "OR (p.customer_type_ids='2')";
		}
		$query = "SELECT count(*) as count FROM $shop_db.products_variants as pv
		INNER JOIN $shop_db.products_category as pc ON pc.product_id=pv.parent_id  
		INNER JOIN  $shop_db.products as p ON p.id = pv.parent_id
		WHERE  pv.attr_id=$attr_id AND pv.attr_value IN ($option_ids_string) AND pc.category_ids=?  AND p.status=1 AND p.remove_flag=0  AND p.launch_date <= ? AND ((FIND_IN_SET($customer_type_id,p.customer_type_ids)) OR (p.customer_type_ids='0') $sub_query)  ";

		$Row = $this->dbl->dbl_conn->rawQueryOne($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $Row['count'];
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

	  public function GetTotalQuoteAddedInventoryExceptCurrentId($QuoteId,$bundle_product_id='',$product_id='',$product_parent_id=''){

		$cart_obj = new DbCart();
		//same item as normal item in cart
		$normal_quantity = 0;

		if($bundle_product_id != ''){
			$checkItemExist=$cart_obj->checkQuoteItemDataExistById($QuoteId,$product_id,$product_parent_id);
			if($checkItemExist !=false){
				$normal_quantity += $checkItemExist['qty_ordered'];
			}
		}
		//same item exists in another bundle in cart
		$otherbundlechild_quantity = 0;
		$checkItemExist1=$cart_obj->checkQuoteItemDataExistInOtherBundle($QuoteId,$bundle_product_id,$product_id,$product_parent_id);
		if($checkItemExist1 != false){
			$arr=array_column($checkItemExist1,"product_id");
			$product_bundle_ids = implode (",", $arr);
			$otherbundlechild_quantity =$cart_obj->getTotalDefaultQtyofBundle($QuoteId,$product_bundle_ids,$product_id,$product_parent_id);
		}
		$total_other_qty = $otherbundlechild_quantity+$normal_quantity;
		return $total_other_qty;
	}

	public function bundleProduct($product_id)
	{
		$param = array($product_id);
	  $query = "SELECT products_bundles.* FROM products_bundles WHERE products_bundles.bundle_product_id = ? ORDER BY position";
		$bundle_product = $this->dbl->dbl_conn->rawQuery($query,$param);

	  if ($this->dbl->dbl_conn->getLastErrno() === 0){
		  if ($this->dbl->dbl_conn->count > 0){
			  return $bundle_product;
		  }else{
			  return false;
		  }
	  }else{
		  return false;
	  }

	}

	public function bundleProductById($id)
	{
		$param = array($id);

	  $query = "SELECT products_bundles.* FROM products_bundles WHERE products_bundles.id = ?";


		$bundle_product = $this->dbl->dbl_conn->rawQueryOne($query,$param);



	  if ($this->dbl->dbl_conn->getLastErrno() === 0){
		  if ($this->dbl->dbl_conn->count > 0){
			  return $bundle_product;
		  }else{
			  return false;
		  }
	  }else{
		  return false;
	  }

	}

	public function bundleProductItemByIdWithInventory($main_bundle_id,$id){

		$param = array($id,$main_bundle_id);
		$query = "SELECT * FROM products_bundles JOIN products_inventory ON products_bundles.product_id = products_inventory.product_id WHERE products_bundles.product_id = ? AND products_bundles.bundle_product_id = ?";

		$bundle_product = $this->dbl->dbl_conn->rawQueryOne($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $bundle_product;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function getproductStatusById($product_id)
	{

		$product_ids_string = implode(',',$product_id);
	  $query = "SELECT id,name,status,remove_flag FROM products WHERE products.id IN ($product_ids_string)";

		$product_detail = $this->dbl->dbl_conn->rawQuery($query);

	  if (($this->dbl->dbl_conn->getLastErrno() === 0) && $this->dbl->dbl_conn->count > 0) {
		  return $product_detail;
	  }

	  return false;
  }

  public function getVariantOptionsSelected($parent_product_id,$attr_ids,$attr_value,$selected_variant_count){

	$attr_ids_string = implode(',',$attr_ids);
	$attr_value_string = implode(',',$attr_value);

	  $param = array($parent_product_id);
	$query = "SELECT DISTINCT (pv.product_id) FROM products_variants as pv INNER JOIN eav_attributes_options as eav_attr_opt ON eav_attr_opt.id = pv.attr_value WHERE pv.parent_id = ? AND  pv.attr_id IN ($attr_ids_string) AND  pv.attr_value IN ($attr_value_string)";

	if($selected_variant_count > 1){
		$query .= " GROUP BY pv.product_id HAVING COUNT(pv.product_id) > 1";
	}
	$variant_option = $this->dbl->dbl_conn->rawQuery($query,$param);

	if ($this->dbl->dbl_conn->getLastErrno() === 0){
		if ($this->dbl->dbl_conn->count > 0){
			return $variant_option;
		}else{
			return false;
		}
	}else{
		return false;
	}
  }

  
  public function productVariantOptionsFilterByProductIds($product_id,$child_ids,$variant_id,$childProductsNotStock='')
  {
	$child_ids = implode(',',$child_ids);

	  $param = array($product_id,$variant_id);

	$query = "SELECT DISTINCT  pv.attr_value, eav_attr_opt.attr_options_name, eav_attr_opt.position, pv.product_id, prod.gift_id, prod.sub_issues, prod.webshop_price, gift.name as gift_master_name FROM products_variants as pv INNER JOIN eav_attributes_options as eav_attr_opt ON eav_attr_opt.id = pv.attr_value INNER JOIN products as prod On pv.product_id = prod.id 
	INNER JOIN gift_master as gift On prod.gift_id = gift.id WHERE pv.parent_id=? AND pv.attr_id=?  AND pv.product_id in ($child_ids) ";
	if ($childProductsNotStock ) {
		$query .= " AND pv.product_id NOT IN ($childProductsNotStock)";
	}
	$query .= " ORDER BY eav_attr_opt.position ASC";
	//$variant_option = $this->dbl->dbl_conn->query($query);
	// echo $product_id;
	// echo $variant_id;
	// echo $query;
	// exit;
	$variant_option = $this->dbl->dbl_conn->rawQuery($query,$param);
	if ($this->dbl->dbl_conn->getLastErrno() === 0){
		if ($this->dbl->dbl_conn->count > 0){
			return $variant_option;
		}else{
			return false;
		}
	}else{
		return false;
	}
}
public function getGiftMaster($giftId){

	$query = "SELECT gift.name  FROM gift_master as gift WHERE gift.id = $giftId";

	$gift_details = $this->dbl->dbl_conn->rawQueryOne($query);

  if ($this->dbl->dbl_conn->getLastErrno() === 0){
	  if ($this->dbl->dbl_conn->count > 0){
		  return $gift_details;
	  }else{
		  return false;
	  }
  }else{
	  return false;
  }
}
public function productExists( $product_id)
{

	$query = "SELECT EXISTS(SELECT * FROM products WHERE id = ?) as product_exists";
	$result = $this->dbl->dbl_conn->rawQueryOne($query, [$product_id]);

	return $result['product_exists'];
}

}
