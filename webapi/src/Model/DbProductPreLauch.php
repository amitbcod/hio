<?php
Class DbProductPreLauch{
	private $dbl;

	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}

    public function productListing($shopcode,$shop_id,$product_id,$customer_type_id='',$options='',$page='',$page_size='',$lang_code='')
  	{

        $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$param = array(0);
  		$limit_var='';

        if(!empty($page) || !empty($page_size)){
			$limit_var=" LIMIT $page , $page_size";
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

		$price_sorting = '';
		$special_price_sorting = '';
		$date_sp = strtotime(date('Y-m-d'));
        if($options=='price_des'){
			$price_sorting = ',( select min(webshop_price) from '.$shop_db.'.products where parent_id = p.id) AS price_sorting_configurable,(SELECT min(webshop_price) FROM '.$shop_db.'.products WHERE id = p.id) AS price_sorting_simple';
		  	$special_price_sorting = ',(SELECT min(special_price) FROM '.$shop_db.'.products_special_prices WHERE (product_id = p.id) AND customer_type_id = '.$customer_type_id.' AND (special_price_from <= '.$date_sp.' AND special_price_to >= '.$date_sp.')) AS special_price_sorting_simple, (SELECT min(special_price) FROM '.$shop_db.'.products_special_prices WHERE product_id IN (SELECT id FROM '.$shop_db.'.products WHERE parent_id = p.id)	AND customer_type_id = '.$customer_type_id.' AND (special_price_from <= '.$date_sp.' AND special_price_to >= '.$date_sp.')) AS special_price_sorting_configurable';
		 	$order_by = ' ORDER BY CASE WHEN product_type = "configurable" THEN	CASE WHEN special_price_sorting_configurable IS NULL THEN price_sorting_configurable ELSE special_price_sorting_configurable END ELSE CASE WHEN special_price_sorting_simple IS NULL THEN price_sorting_simple ELSE	special_price_sorting_simple END END DESC';
           //$order_by = ' ORDER BY p.webshop_price DESC';
        }else if($options=='price_asc'){
			$price_sorting = ',( select min(webshop_price) from '.$shop_db.'.products where parent_id = p.id) AS price_sorting_configurable,(SELECT min(webshop_price) FROM '.$shop_db.'.products WHERE id = p.id) AS price_sorting_simple';
			$special_price_sorting = ',(SELECT min(special_price) FROM '.$shop_db.'.products_special_prices WHERE (product_id = p.id) AND customer_type_id = '.$customer_type_id.' AND (special_price_from <= '.$date_sp.' AND special_price_to >= '.$date_sp.')) AS special_price_sorting_simple, (SELECT min(special_price) FROM '.$shop_db.'.products_special_prices WHERE product_id IN (SELECT id FROM '.$shop_db.'.products WHERE parent_id = p.id)	AND customer_type_id = '.$customer_type_id.' AND (special_price_from <= '.$date_sp.' AND special_price_to >= '.$date_sp.')) AS special_price_sorting_configurable';
			$order_by = ' ORDER BY CASE WHEN product_type = "configurable" THEN	CASE WHEN special_price_sorting_configurable IS NULL THEN price_sorting_configurable ELSE special_price_sorting_configurable END ELSE CASE WHEN special_price_sorting_simple IS NULL THEN price_sorting_simple ELSE	special_price_sorting_simple END END ASC';
            //$order_by = ' ORDER BY p.webshop_price ASC';
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
		$p_select_list='p.name,p.gender,p.price,p.id,p.product_inv_type,p.shop_id,p.special_price,p.special_price_from,p.special_price_to,p.webshop_price,p.url_key,p.product_type,p.product_code,p.highlights,p.search_keywords,p.promo_reference,p.launch_date,p.customer_type_ids,p.status,p.remove_flag,p.customer_type_ids,p.base_image,p.shop_product_id';
		$query = "SELECT $p_select_list $lang_select_data $price_sorting $special_price_sorting FROM $shop_db.products as p $lang_query WHERE ((p.product_type = 'simple') OR (p.product_type = 'configurable') OR (p.product_type = 'bundle')) AND ((FIND_IN_SET($customer_type_id,p.customer_type_ids)) OR (p.customer_type_ids='0') $sub_query) AND p.remove_flag = ? $product_in $order_by $limit_var";

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

    public function productListingCount($shopcode,$shop_id,$product_id,$customer_type_id='',$options=''){

        $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$param = array(0);

		$product_in = '';
		if($product_id != ''){
  			$product_in = "AND p.id IN (".$product_id.")";
  		}

		$sub_query='';
		if($customer_type_id > 2 )
		{
			$sub_query = "OR (p.customer_type_ids='2')";
		}

        if($options=='price_des'){
            $order_by = ' ORDER BY p.webshop_price DESC';
        }else if($options=='price_asc'){
            $order_by = ' ORDER BY p.webshop_price ASC';
        }else if($options=='popular'){
          $order_by = ' ORDER BY p.launch_date DESC, p.id DESC ';
        }else{
            $order_by = ' ORDER BY p.launch_date DESC, p.id DESC ';
        }

		$query = "SELECT p.* FROM $shop_db.products as p WHERE ((p.product_type = 'simple') OR (p.product_type = 'configurable') OR (p.product_type = 'bundle')) AND ((FIND_IN_SET($customer_type_id,p.customer_type_ids)) OR (p.customer_type_ids='0') $sub_query) AND p.remove_flag = ? $product_in $order_by";

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


}
