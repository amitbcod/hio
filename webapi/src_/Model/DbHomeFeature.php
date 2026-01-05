<?php
Class DbHomeFeature{
	private $dbl;

	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}


	public function getShopBanners($banner_type,$category_id='',$lang_code='')
	{
	 	$category_in = '';
	  if($category_id != ''){
			$category_in = "AND FIND_IN_SET(".$category_id.", $shop_db.banners.category_ids)";
		}
	  $current_date = date('Y-m-d');
	  $param = array($banner_type,1);
	  if($lang_code !='')
		{
		$query = "SELECT banners.*,mlb.heading as lang_homeblock_heading,mlb.description as lang_homeblock_description,mlb.button_text as lang_homeblock_button_text FROM banners LEFT JOIN multi_lang_banners as mlb ON (banners.id=mlb.banner_id and mlb.lang_code='$lang_code') INNER JOIN static_blocks ON static_blocks.id=banners.static_block_id WHERE static_blocks.identifier = ?
		  AND (((banners.start_date = '0000-00-00' OR banners.start_date is NULL) AND (banners.end_date ='0000-00-00' OR banners.end_date is NULL)) OR (banners.start_date <= '$current_date' AND banners.end_Date >= '$current_date'))
		  AND banners.status=1 AND static_blocks.status = ? $category_in ORDER BY banners.position ASC";
	  }
	  else{
		  $query = "SELECT banners.* FROM banners INNER JOIN static_blocks ON static_blocks.id=banners.static_block_id WHERE static_blocks.identifier = ?
		  AND (((banners.start_date = '0000-00-00' OR banners.start_date is NULL) AND (banners.end_date ='0000-00-00' OR banners.end_date is NULL)) OR (banners.start_date <= '$current_date' AND banners.end_Date >= '$current_date'))
		  AND banners.status=1 AND static_blocks.status = ? $category_in ORDER BY banners.position ASC";
	  }

	  $get_banner = $this->dbl->dbl_conn->rawQuery($query,$param);
	  if ($this->dbl->dbl_conn->getLastErrno() === 0){
		  if ($this->dbl->dbl_conn->count > 0){
			  return $get_banner;
		  }else{
			  return false;
		  }
	  }else{
		  return false;
	  }

}

  	public function promoTextBanners($country_code,$lang_code='')
  	{
  		$param = array(1);
  		if($lang_code !='')
  		{
  			$query = "SELECT promo_text_banners.*,mpt.banner_text as lang_banner_text FROM promo_text_banners LEFT JOIN multi_lang_promo_text_banners as mpt ON (promo_text_banners.id=mpt.banner_id and mpt.lang_code='$lang_code') WHERE FIND_IN_SET('$country_code', promo_text_banners.country_code) AND promo_text_banners.status = ? ORDER BY promo_text_banners.id ASC LIMIT 1";
  		}
  		else
  		{
  			$query = "SELECT promo_text_banners.* FROM promo_text_banners WHERE FIND_IN_SET('$country_code', promo_text_banners.country_code) AND promo_text_banners.status = ? ORDER BY promo_text_banners.id ASC LIMIT 1";
  	    }

		$get_promo = $this->dbl->dbl_conn->rawQueryOne($query, $param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $get_promo;
			}else{
				return false;
			}
		}else{
			return false;
		}

  }


  	public function getStaticBlock($identifier,$lang_code='')
  	{
  		$param = array($identifier,1);
  		if($lang_code !='')
  		{
  			$query = "SELECT static_blocks.*,msb.content as lang_static_content,msb.title as lang_static_title FROM static_blocks LEFT JOIN multi_lang_static_blocks as msb ON (static_blocks.id=msb.block_id and msb.lang_code='$lang_code') WHERE static_blocks.identifier = ? AND static_blocks.status = ?";
  		}
  		else
  		{
  			$query = "SELECT static_blocks.* FROM static_blocks WHERE static_blocks.identifier = ? AND static_blocks.status = ?";

  		}

		$get_static_block = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $get_static_block;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

	public function getNewArrivalList($shopcode,$shop_id,$customer_type_id,$limit='',$product_id='',$lang_code='',$flag_rr='',$page='',$options='',$badge='',$gender=array(),$price_range=array(),$variant_id_arr=array(),$variant_attr_value_arr=array(),$attribute_arr=array())
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		$date = strtotime(date('Y-m-d'));
		$param = array($date,1,0);
  		$limit_var='';

  		if($limit != ''){
  			$limit_var = 'LIMIT '.$limit;
  		}

  		if ($page != '') {
  			$limit_var = 'LIMIT '.$page.','.$limit;
  		}
		$product_in = '';
		if($product_id != ''){
  			$product_in = "AND p.id IN (".$product_id.")";
  		}

		$sub_query='';
		if($customer_type_id > 2 )
		{
			$sub_query = "OR (p.customer_type_ids='2')";
		}

		$badge_query = '';
		if($badge != ''){
			$Date = date('Y-m-d');
			$from_date = strtotime($Date. ' - 45 days');
			$badge_query = 'AND p.launch_date >= ' . $from_date;
		}

		$popular_count = '';
		$popular = '';
		$price_sorting = ''; //
		$special_price_sorting = '';
		$date_sp = strtotime(date('Y-m-d'));

		if($options=='price_des'){
        	$price_sorting = ',( select min(webshop_price) from '.$shop_db.'.products where parent_id = p.id) AS price_sorting_configurable,(SELECT min(webshop_price) FROM '.$shop_db.'.products WHERE id = p.id) AS price_sorting_simple';

			$special_price_sorting = ',(SELECT min(special_price) FROM '.$shop_db.'.products_special_prices WHERE (product_id = p.id) AND customer_type_id = '.$customer_type_id.' AND (special_price_from <= '.$date_sp.' AND special_price_to >= '.$date_sp.')) AS special_price_sorting_simple, (SELECT min(special_price) FROM '.$shop_db.'.products_special_prices WHERE product_id IN (SELECT id FROM '.$shop_db.'.products WHERE parent_id = p.id) AND customer_type_id = '.$customer_type_id.' AND (special_price_from <= '.$date_sp.' AND special_price_to >= '.$date_sp.')) AS special_price_sorting_configurable';

			$order_by = ' ORDER BY CASE WHEN product_type = "configurable" THEN CASE WHEN special_price_sorting_configurable IS NULL THEN price_sorting_configurable ELSE special_price_sorting_configurable END ELSE CASE WHEN special_price_sorting_simple IS NULL THEN price_sorting_simple ELSE	special_price_sorting_simple END END DESC';
        	}else if($options=='price_asc'){
            		$price_sorting = ',( select min(webshop_price) from '.$shop_db.'.products where parent_id = p.id) AS price_sorting_configurable,(SELECT min(webshop_price) FROM '.$shop_db.'.products WHERE id = p.id) AS price_sorting_simple';

			$special_price_sorting = ',(SELECT min(special_price) FROM '.$shop_db.'.products_special_prices WHERE (product_id = p.id) AND customer_type_id = '.$customer_type_id.' AND (special_price_from <= '.$date_sp.' AND special_price_to >= '.$date_sp.')) AS special_price_sorting_simple, (SELECT min(special_price) FROM '.$shop_db.'.products_special_prices WHERE product_id IN (SELECT id FROM '.$shop_db.'.products WHERE parent_id = p.id) AND customer_type_id = '.$customer_type_id.' AND (special_price_from <= '.$date_sp.' AND special_price_to >= '.$date_sp.')) AS special_price_sorting_configurable';

			$order_by = ' ORDER BY CASE WHEN product_type = "configurable" THEN CASE WHEN special_price_sorting_configurable IS NULL THEN price_sorting_configurable ELSE special_price_sorting_configurable END ELSE CASE WHEN special_price_sorting_simple IS NULL THEN price_sorting_simple ELSE	special_price_sorting_simple END END ASC';
		}else if($options=='popular'){
		  $order_by = ' ORDER BY p.launch_date DESC, p.id DESC ';
		}else{
		    $order_by = ' ORDER BY p.launch_date DESC, p.id DESC ';
		}

		$lang_select_data='';
		$lang_query='';
		if($lang_code !=''){
			$lang_select_data= ', mlp.name as other_lang_name, mlp.highlights as other_lang_highlights, mlp.description as other_lang_description,mlp.meta_description as other_lang_meta_description,mlp.meta_keyword as other_lang_meta_keyword,mlp.meta_title as other_lang_meta_title';

			$lang_query = 'LEFT JOIN '.$shop_db.'.multi_lang_products as mlp ON (p.id=mlp.product_id and mlp.lang_code="'.$lang_code.'")';
		}

		$coming_soon_check = '';
		if($flag_rr=='new_arrivals') {
			$coming_soon_check = ' AND p.coming_soon_flag=0 ';
		}

		$where_gender = '';
		if(!empty($gender)) {
			$str = implode("|",$gender);
			$where_gender = 'AND CONCAT(",",p.gender,",") REGEXP ",('.$str.'),"';
		}

		$where_variant="";
		$distinct="";
		if(!empty($variant_id_arr) && !empty($variant_attr_value_arr)) {
			$str_id = "'".implode("','",$variant_id_arr)."'";
			$str_value = "'".implode("','",$variant_attr_value_arr)."'";

			$where_variant = 'INNER JOIN '.$shop_db.'.products_variants as prv ON prv.parent_id=p.id AND prv.attr_id IN ('.$str_id.') AND prv.attr_value IN ('.$str_value.') INNER JOIN '.$shop_db.'.products_inventory as pin ON pin.product_id='.$shop_db.'.prv.product_id ';
			$distinct = 'DISTINCT prv.parent_id,';

			$inv_check_where =' AND CASE WHEN p.product_type = "configurable" THEN CASE WHEN ((SELECT product_inv_type FROM '.$shop_db.'.products WHERE id=pin.product_id)="virtual" && pin.available_qty <=0) || ((SELECT product_inv_type FROM '.$shop_db.'.products WHERE id=pin.product_id)="dropship") THEN 1 ELSE pin.available_qty END ELSE CASE WHEN ((p.product_inv_type ="virtual" && (SELECT available_qty FROM '.$shop_db.'.products_inventory WHERE product_id=p.id) <=0) || p.product_inv_type = "dropship") THEN 1 ELSE (SELECT available_qty FROM '.$shop_db.'.products_inventory WHERE product_id=p.id) END END > 0 ';

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

			$where_attribute = 'INNER JOIN '.$shop_db.'.products_attributes as p_attr ON p_attr.product_id=p.id  '.$where_attr;
			$distinct = 'DISTINCT p_attr.product_id,';

		}

		$query = "SELECT $distinct p.* $price_sorting $special_price_sorting FROM $shop_db.products as p $lang_query $where_variant $where_attribute WHERE ((p.product_type = 'simple') OR (p.product_type = 'configurable') OR (p.product_type = 'bundle')) AND p.launch_date <= ? $badge_query AND ((FIND_IN_SET($customer_type_id,p.customer_type_ids)) OR (p.customer_type_ids='0') $sub_query) AND p.status = ? AND p.remove_flag = ? $where_gender $product_in $coming_soon_check $inv_check_where $order_by $limit_var";

		$new_arrival_product = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $new_arrival_product;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	public function getNewArrivalList_product_count($shopcode,$shop_id,$customer_type_id,$product_id='',$lang_code='',$flag_rr='',$options='',$badge='',$gender=array(),$price_range=array(),$variant_id_arr=array(),$variant_attr_value_arr=array(),$attribute_arr=array())
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$date = strtotime(date('Y-m-d'));
		$param = array($date,1,0);

		$product_in = '';
		if($product_id != ''){
  			$product_in = "AND p.id IN (".$product_id.")";
  		}

		$sub_query='';
		if($customer_type_id!='' && $customer_type_id > 2 )
		{
			$sub_query = "OR (p.customer_type_ids='2')";
		}

		$popular_count = '';
		$popular = '';
		if($options=='price_des'){
            $order_by = ' ORDER BY p.webshop_price DESC';
		}else if($options=='price_asc'){
            $order_by = ' ORDER BY p.webshop_price ASC';
		}else if($options=='popular'){
			$order_by = ' ORDER BY p.launch_date DESC, p.id DESC ';
		}else{
			$order_by = ' ORDER BY p.launch_date DESC, p.id DESC ';
		}

		$coming_soon_check = '';
		if($flag_rr=='new_arrivals') {
			$coming_soon_check = ' AND p.coming_soon_flag=0 ';
		}

		$badge_query = '';
		if($badge != ''){
			$Date = date('Y-m-d');
			$from_date = strtotime($Date. '- 45 days');
			$badge_query = 'AND p.launch_date >= ' . $from_date;
		}

		$where_gender = '';
		if(!empty($gender)) {
			$str = implode("|",$gender);
			$where_gender = 'AND CONCAT(",",p.gender,",") REGEXP ",('.$str.'),"';
		}

		$where_variant="";
		$distinct="";
		if(!empty($variant_id_arr) && !empty($variant_attr_value_arr)) {
			$str_id = "'".implode("','",$variant_id_arr)."'";
			$str_value = "'".implode("','",$variant_attr_value_arr)."'";
			$where_variant = 'INNER JOIN '.$shop_db.'.products_variants as prv ON prv.parent_id=p.id AND prv.attr_id IN ('.$str_id.') AND prv.attr_value IN ('.$str_value.') INNER JOIN '.$shop_db.'.products_inventory as pin ON pin.product_id='.$shop_db.'.prv.product_id ';
			$distinct = 'DISTINCT prv.parent_id,';

			$inv_check_where =' AND CASE WHEN p.product_type = "configurable" THEN CASE WHEN ((SELECT product_inv_type FROM '.$shop_db.'.products WHERE id=pin.product_id)="virtual" && pin.available_qty <=0) || ((SELECT product_inv_type FROM '.$shop_db.'.products WHERE id=pin.product_id)="dropship") THEN 1 ELSE pin.available_qty END ELSE CASE WHEN ((p.product_inv_type ="virtual" && (SELECT available_qty FROM '.$shop_db.'.products_inventory WHERE product_id=p.id) <=0) || p.product_inv_type = "dropship") THEN 1 ELSE (SELECT available_qty FROM '.$shop_db.'.products_inventory WHERE product_id=p.id) END END > 0 ';

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

			$where_attribute = 'INNER JOIN '.$shop_db.'.products_attributes as p_attr ON p_attr.product_id=p.id  '.$where_attr;
			$distinct = 'DISTINCT p_attr.product_id,';

		}

		$query = "SELECT count(*) as p_count FROM $shop_db.products as p $where_variant $where_attribute $popular WHERE (p.product_type IN ('simple','configurable')) $where_gender AND p.launch_date <= ? $coming_soon_check $badge_query AND ((FIND_IN_SET($customer_type_id,p.customer_type_ids)) OR (p.customer_type_ids='0') $sub_query) AND p.status = ? AND p.remove_flag = ? $inv_check_where $product_in GROUP BY p.id $order_by ";
		$new_arrival_product = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return count($new_arrival_product);
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	public function  getproductscountsbycategoryid($category_id)
  	{
  		$date = strtotime(date('d-m-Y'));

  		$param = array($category_id,$date,0,1);

  		$query = "SELECT COUNT(prod.id) as product_count from products as prod, products_category as pc where prod.id = pc.product_id
			AND pc.category_ids= ? AND prod.launch_date <= ? AND prod.remove_flag=? and prod.status=?";

  		$product_count = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $product_count;
			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

  	public function getAllCategories($blockID,$Identifier='',$lang_code='')
  	{
  		if($lang_code!=''){
				$param = array($lang_code,1,0,$blockID);
				$query = "SELECT mlc.cat_name as lang_cat_name,main_cat.id,main_cat.cat_name,main_cat.cat_level,main_cat.slug,wcm.category_id FROM category as main_cat INNER JOIN webshop_cat_menus as wcm ON main_cat.id=wcm.category_id LEFT JOIN multi_lang_category as mlc ON (main_cat.id=mlc.category_id and mlc.lang_code=?)   WHERE main_cat.status=? AND main_cat.cat_level=? AND wcm.static_block_id=?";
			}else{
  			$param = array(1,0,$blockID);

  			$query = "SELECT main_cat.id,main_cat.cat_name,main_cat.cat_level,main_cat.slug,wcm.category_id FROM category as main_cat INNER JOIN webshop_cat_menus as wcm ON main_cat.id=wcm.category_id WHERE main_cat.status=? AND main_cat.cat_level=? AND wcm.static_block_id=?";
			}

		$mainCatMenu = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->count > 0)
		{
			$final_arr = array();

			foreach($mainCatMenu as $cat)
			{
				$arr = array();
				$arr['id'] = $cat['id'];
				$arr['menu_name'] = $cat['cat_name'];
				if(!empty($lang_code) && isset($cat['lang_cat_name'])){
					$arr['lang_menu_name'] = $cat['lang_cat_name'];
				}
				$arr['menu_level'] = $cat['cat_level'];
				$arr['slug'] = $cat['slug'];
				$arr['category_id'] = $cat['category_id'];

				if($Identifier !='' && $Identifier !=false && $Identifier =='categorymenu') {

					$product_count = $this->getproductscountsbycategoryid($arr['category_id'] );
					if ($product_count[0]['product_count'] == 0) {
						continue;
					}
					$arr['product_count'] = $product_count[0]['product_count'];

				}

				$firstLevelCategory = $this->firstLevelCategory($cat['id'],$blockID,$lang_code);

				if($firstLevelCategory != false)
				{
					foreach($firstLevelCategory as $cat1)
					{
						$arr1 = array();
						$arr1['id'] = $cat1['id'];
						$arr1['menu_name'] = $cat1['cat_name'];
						if(!empty($lang_code) && isset($cat1['lang_cat_name'])){
							$arr1['lang_menu_name'] = $cat1['lang_cat_name'];
						}
						$arr1['menu_level'] = $cat1['cat_level'];
						$arr1['slug'] = $cat1['slug'];
						$arr1['category_id'] = $cat1['category_id'];
						// if($Identifier !='' && $Identifier !=false ) {
						// 	$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);
						// 	echo "<pre>";
						// 	print_r($customer_type_id);
						// 	echo "<br>";
						// 	print_r($Identifier);
						// 	echo "<br>";
						// 	print_r($arr1['category_id']);
						// 	$product_count1 = $this->getproductscountsbycategoryid($shopcode,$arr1['category_id'],$customer_type_id );
						// 	print_r($product_count1);
						// 	$arr1['product_count1'] = $product_count1[0]['product_count'];
						// }
						$secondLevelCategory = $this->secondLevelCategory($cat1['id'],$cat['id'],$blockID,$lang_code);
						if($secondLevelCategory != false)
						{
							foreach($secondLevelCategory as $cat2) {
								$arr2['id'] = $cat2['id'];
								$arr2['menu_name'] = $cat2['cat_name'];
								if(!empty($lang_code) && isset($cat2['lang_cat_name'])){
									$arr2['lang_menu_name'] = $cat2['lang_cat_name'];
								}
								$arr2['menu_level'] = $cat2['cat_level'];
								$arr2['slug'] = $cat2['slug'];
								$arr2['category_id'] = isset($cat2['category_id']) ? $cat2['category_id'] : '';
							// 	if($Identifier !='' && $Identifier !=false ) {
							// 		$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);
							// 		echo "<pre>";
							// print_r($customer_type_id);
							// echo "<br>";
							// print_r($Identifier);
							// echo "<br>";
							// print_r($arr2['category_id']);
							// 		$product_count2 = $this->getproductscountsbycategoryid($shopcode,$arr2['category_id'],$customer_type_id );
							// print_r($product_count2);
							// 		$arr2['product_count2'] = $product_count2[0]['product_count'];
							// 	}
								$arr1['menu_level_2'][] = $arr2;
							}
						}

						$arr['menu_level_1'][] = $arr1;
					}
				}

				$final_arr[] = $arr;
			}
			return $final_arr;
		}else{
			return false;
		}

  	}

  	public function firstLevelCategory($category_id,$blockID,$lang_code='')
	{
		if($lang_code!=''){
			$param = array($lang_code,1,1,$category_id,$blockID);
			$query = "SELECT mlc.cat_name as lang_cat_name,cat_level1.id,cat_level1.cat_name,cat_level1.cat_level,cat_level1.slug,wcm.category_id
			FROM category as cat_level1
			INNER JOIN webshop_cat_menus as wcm ON cat_level1.id=wcm.category_id
			LEFT JOIN multi_lang_category as mlc ON (cat_level1.id=mlc.category_id and mlc.lang_code=?)
			WHERE cat_level1.status=? AND cat_level1.cat_level=? AND cat_level1.parent_id=? AND wcm.static_block_id=? ORDER BY wcm.position,wcm.id";
		}else{
  			$param = array(1,1,$category_id,$blockID);

  			$query = "SELECT cat_level1.id,cat_level1.cat_name,cat_level1.cat_level,cat_level1.slug,wcm.category_id
			FROM category as cat_level1
			INNER JOIN webshop_cat_menus as wcm ON cat_level1.id=wcm.category_id
			WHERE cat_level1.status=? AND cat_level1.cat_level=? AND cat_level1.parent_id=? AND wcm.static_block_id=? ORDER BY wcm.position,wcm.id";
		}

		$level1CatMenu = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $level1CatMenu;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function secondLevelCategory($cat_parent_id,$cat_main_parent_id,$blockID,$lang_code='')
	{
		if($lang_code!=''){

			$param = array($lang_code,1,2,$cat_parent_id,$cat_main_parent_id,$blockID);

  			$query = "SELECT mlc.cat_name as lang_cat_name,cat_level2.id,cat_level2.cat_name,cat_level2.cat_level,cat_level2.slug,wcm.category_id
			FROM category as cat_level2
			INNER JOIN webshop_cat_menus as wcm ON cat_level2.id=wcm.category_id
			LEFT JOIN multi_lang_category as mlc ON (cat_level2.id=mlc.category_id and mlc.lang_code=?)
			WHERE cat_level2.status=? AND cat_level2.cat_level=? AND cat_level2.parent_id=? AND cat_level2.main_parent_id=? AND wcm.static_block_id=? ORDER BY wcm.position,wcm.id";

		}else{

  			$param = array(1,2,$cat_parent_id,$cat_main_parent_id,$blockID);

  			$query = "SELECT cat_level2.id,cat_level2.cat_name,cat_level2.cat_level,cat_level2.slug,wcm.category_id
			FROM $main_db.category as cat_level2
			INNER JOIN $shop_db.webshop_cat_menus as wcm ON cat_level2.id=wcm.category_id
			WHERE cat_level2.status=? AND cat_level2.cat_level=? AND cat_level2.parent_id=? AND cat_level2.main_parent_id=? AND wcm.static_block_id=? ORDER BY wcm.position,wcm.id";
		}

		$level2CatMenu = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $level2CatMenu;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function getCustomMenus($blockID,$lang_code='')
	{
		if(!empty($lang_code)){
			$param = [$lang_code,$blockID];
			$query = "SELECT wcm.*, mlwcm.menu_name as lang_menu_name FROM webshop_custom_menus as wcm
			LEFT JOIN multi_lang_webshop_custom_menus as mlwcm on ( mlwcm.menu_id = wcm.id and mlwcm.lang_code = ? )
			WHERE  wcm.static_block_id=? AND wcm.status=1 ORDER BY wcm.position,wcm.id";
 		} else {
  			$param = [$blockID];
			$query = "SELECT wcm.* FROM webshop_custom_menus as wcm WHERE wcm.static_block_id=? AND wcm.status= 1 ORDER BY wcm.position,wcm.id";
		}

		$fullMenu = $this->dbl->dbl_conn->rawQuery($query,$param);

        if ($this->dbl->dbl_conn->count <= 0) {
            return false;
        }

        $slugs = $this->loadMenuSlugs($fullMenu);

        $mainCustMenu = array_filter($fullMenu, function($menuItem) {
           return $menuItem['menu_level'] === 0;
        });

        $final_arr = [];

        foreach ($mainCustMenu as $menu) {
            $arr = [];
            $arr['id'] = $menu['id'];
            $arr['menu_name'] = $menu['menu_name'];
            if (!empty($lang_code) && isset($menu['lang_menu_name'])) {
                $arr['lang_menu_name'] = $menu['lang_menu_name'];
            }
            $arr['menu_level'] = $menu['menu_level'];
            $arr['menu_type'] = $menu['menu_type'];
            $arr['category_id'] = $menu['category_id'];

            $arr['slug'] = $slugs[$menu['id']];
            $firstLevelMenu = array_filter($fullMenu, function($menuItem) use ($menu) {
               return $menuItem['menu_parent_id'] === $menu['id'];
            });

            if ($firstLevelMenu != false) {
                foreach ($firstLevelMenu as $menu1) {

                    $arr1 = array();
                    $arr1['id'] = $menu1['id'];
                    $arr1['menu_name'] = $menu1['menu_name'];
                    if (!empty($lang_code) && isset($menu1['lang_menu_name'])) {
                        $arr1['lang_menu_name'] = $menu1['lang_menu_name'];
                    }
                    $arr1['menu_level'] = $menu1['menu_level'];
                    $arr1['menu_type'] = $menu1['menu_type'];
                    $arr1['category_id'] = $menu1['category_id'];

                    $arr1['slug'] = $slugs[$menu1['id']];

                    $secondLevelMenu = array_filter($fullMenu, function($menuItem) use ($menu1) {
                        return $menuItem['menu_parent_id'] === $menu1['id'];
                    });

                    foreach ($secondLevelMenu as $menu2) {
                        $arr2 = [];
                        $arr2['id'] = $menu2['id'];
                        $arr2['menu_name'] = $menu2['menu_name'];
                        if (!empty($lang_code) && isset($menu2['lang_menu_name'])) {
                            $arr2['lang_menu_name'] = $menu2['lang_menu_name'];
                        }
                        $arr2['menu_level'] = $menu2['menu_level'];
                        $arr2['menu_type'] = $menu2['menu_type'];
                        $arr2['category_id'] = $menu2['category_id'] ?? '';

                        $arr2['slug'] = $slugs[$menu2['id']];

                        $thirdLevelMenu = array_filter($fullMenu, function($menuItem) use ($menu2) {
                            return $menuItem['menu_parent_id'] === $menu2['id'];
                        });

                        foreach ($thirdLevelMenu as $menu3) {
                            $arr3['id'] = $menu3['id'];
                            $arr3['menu_name'] = $menu3['menu_name'];
                            if (!empty($lang_code) && isset($menu3['lang_menu_name'])) {
                                $arr3['lang_menu_name'] = $menu3['lang_menu_name'];
                            }
                            $arr3['menu_level'] = $menu3['menu_level'];
                            $arr3['menu_type'] = $menu3['menu_type'];
                            $arr3['category_id'] = isset($menu3['category_id']) ? $menu3['category_id'] : '';
                            $arr3['slug'] = $slugs[$menu3['id']];

                            $arr2['menu_level_3'][] = $arr3;
                        }
                        $arr1['menu_level_2'][] = $arr2;
                    }
                    $arr['menu_level_1'][] = $arr1;
                }
            }

            $final_arr[] = $arr;
        }

        return $final_arr;
    }

    private function loadMenuSlugs(array $fullMenu)
    {
        $category_menus = array_filter($fullMenu, function($menuItem){
           return $menuItem['menu_type'] === 3;
        });

        $cms_menus = array_filter($fullMenu, function($menuItem){
            return $menuItem['menu_type'] === 2;
        });

        $custom_link_menus = array_filter($fullMenu, function($menuItem){
           return $menuItem['menu_type'] !== 2 && $menuItem['menu_type'] !== 3;
        });

        $slugs = [];

        if(count($cms_menus) > 0){
            $params = [implode(',',array_column($cms_menus, 'page_id'))];
            $query = "SELECT pg.* FROM cms_pages as pg WHERE pg.id IN (?) AND pg.status= 1";
            $pageData = $this->dbl->dbl_conn->rawQuery($query, $params);
            $pageSlugData = array_combine(array_column($pageData, 'id'), array_column($pageData, 'identifier'));
            foreach($cms_menus as $cms_menu){
                $slugs[$cms_menu['id']] = $pageSlugData[$cms_menu['page_id']];
            }
        }

        if(count($category_menus) > 0) {
            $catData = $this->dbl->dbl_conn
                ->where('status', 1)
                ->where('id', array_column($category_menus, 'category_id'), 'IN')
                ->get("category");

            $catSlugData = array_combine(array_column($catData, 'id'), array_column($catData, 'slug'));

            foreach($category_menus as $category_menu){
                $slugs[$category_menu['id']] = $catSlugData[$category_menu['category_id']] ?? '-';
            }
        }

        if(count($custom_link_menus) > 0) {
          foreach($custom_link_menus as $custom_link_menu){
              $slugs[$custom_link_menu['id']] = $custom_link_menu['menu_custom_url'];
          }
        }

        return $slugs;
    }

	public function getDataByMenuType($shopcode,$mainCustMenu)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		if ($this->dbl->dbl_conn->getLastErrno() !== 0) {
            return false;
        }

        if ($this->dbl->dbl_conn->count <= 0) {
            return false;
        }

        $slugValue = '';
        if ($mainCustMenu['menu_type'] == 2) {
            $params = array($mainCustMenu['page_id'], 1);
            $query1 = "SELECT pg.* FROM $shop_db.cms_pages as pg WHERE pg.id=? AND pg.status=?";
            $pageData = $this->dbl->dbl_conn->rawQueryOne($query1, $params);
            if ($this->dbl->dbl_conn->count > 0) {
                $slugValue = $pageData['identifier'];
            }
        } else if ($mainCustMenu['menu_type'] == 3) {
            $param1 = array($mainCustMenu['category_id'], 1);
            $query2 = "SELECT cat.* FROM $main_db.category as cat WHERE cat.id=? AND cat.status=?";
            $catData = $this->dbl->dbl_conn->rawQueryOne($query2, $param1);
            if ($this->dbl->dbl_conn->count > 0) {
                $slugValue = $catData['slug'];
            }
        } else {
            $slugValue = $mainCustMenu['menu_custom_url'];
        }

        return $slugValue;
    }


	public function firstLevelMenu($shopcode,$menu_id,$blockID,$lang_code='')
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		if($lang_code!=''){
			$param = array($lang_code,$blockID,1,$menu_id,1);

			$query = "SELECT wcm.*, mlwcm.menu_name as lang_menu_name FROM $shop_db.webshop_custom_menus as wcm LEFT JOIN $shop_db.multi_lang_webshop_custom_menus as mlwcm on ( mlwcm.menu_id = wcm.id and mlwcm.lang_code = ? ) WHERE wcm.static_block_id=? AND wcm.menu_level=? AND wcm.menu_parent_id=? AND wcm.status=?";

		}else{
  			$param = array($blockID,1,$menu_id,1);

			$query = "SELECT wcm.* FROM $shop_db.webshop_custom_menus as wcm WHERE wcm.static_block_id=? AND wcm.menu_level=? AND wcm.menu_parent_id=? AND wcm.status=?";
		}

		$level1CustMenu = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $level1CustMenu;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}


	public function secondLevelMenu($shopcode,$menu_parent_id,$menu_main_parent_id,$blockID,$lang_code='')
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		if($lang_code!=''){
			$param = array($lang_code,$blockID,2,$menu_parent_id,$menu_main_parent_id,1);

			$query = "SELECT wcm.*, mlwcm.menu_name as lang_menu_name FROM $shop_db.webshop_custom_menus as wcm LEFT JOIN $shop_db.multi_lang_webshop_custom_menus as mlwcm on ( mlwcm.menu_id = wcm.id and mlwcm.lang_code = ? ) WHERE wcm.static_block_id=? AND wcm.menu_level=? AND wcm.menu_parent_id=? AND wcm.menu_main_parent_id=? AND wcm.status=?";

		}else{
  			$param = array($blockID,2,$menu_parent_id,$menu_main_parent_id,1);

			$query = "SELECT wcm.* FROM $shop_db.webshop_custom_menus as wcm WHERE wcm.static_block_id=? AND wcm.menu_level=? AND wcm.menu_parent_id=? AND wcm.menu_main_parent_id=? AND wcm.status=?";
		}

		$level1CustMenu = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $level1CustMenu;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function thirdLevelMenu($shopcode,$menu_parent_id,$menu_main_parent_id,$blockID,$lang_code='')
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		if($lang_code!=''){
			$param = array($lang_code,$blockID,3,$menu_parent_id,$menu_main_parent_id,1);

			$query = "SELECT wcm.*, mlwcm.menu_name as lang_menu_name FROM $shop_db.webshop_custom_menus as wcm LEFT JOIN $shop_db.multi_lang_webshop_custom_menus as mlwcm on ( mlwcm.menu_id = wcm.id and mlwcm.lang_code = ? ) WHERE wcm.static_block_id=? AND wcm.menu_level=? AND wcm.menu_parent_id=? AND wcm.menu_main_parent_id=? AND wcm.status=?";

		}else{
  			$param = array($blockID,3,$menu_parent_id,$menu_main_parent_id,1);

			$query = "SELECT wcm.* FROM $shop_db.webshop_custom_menus as wcm WHERE wcm.static_block_id=? AND wcm.menu_level=? AND wcm.menu_parent_id=? AND wcm.menu_main_parent_id=? AND wcm.status=?";
		}

		$level3CustMenu = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $level3CustMenu;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function getDataByEmail($email)
  	{
  		$param = array($email);
  		$query = "SELECT ns.* FROM newsletter_subscriber as ns WHERE ns.email = ?";

		$get_row = $this->dbl->dbl_conn->rawQueryOne($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $get_row;
			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

  	public function updateDataByEmail($email)
	{
		$updated_at = time();
		$params = array(1,$updated_at,$email);
		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE newsletter_subscriber SET status=?, updated_at=? WHERE email=?",$params);
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

	public function insertData($email)
	{
		$created_at = time();
		$ip = $_SERVER['REMOTE_ADDR'];

		$param = array($email,$created_at,$ip);
		$insert_row = $this->dbl->dbl_conn->rawQueryOne("INSERT INTO newsletter_subscriber (email,created_at,ip) VALUES(?,?,?)",$param);

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

}
