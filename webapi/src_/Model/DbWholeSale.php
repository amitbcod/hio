<?php
Class DbWholeSale{
	private $dbl;

	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}

    public function getInStockProducts($shopcode, $min_available_qty = 0)
    {
        $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
        $main_db = DB_NAME; //Constant variable

        $query = <<<SQL
		SELECT products.id, products.parent_id, products.product_code, products.launch_date, products.sku, products.name, products.barcode,
       				products.price, products.weight, pi.available_qty, parent.launch_date as parent_launch_date, parent.product_code as parent_product_code,
			(SELECT attr_options_name
				FROM $shop_db.products_variants INNER JOIN $main_db.eav_attributes_options ON $shop_db.products_variants.attr_value = $main_db.eav_attributes_options.id
				WHERE product_id = products.id AND $main_db.eav_attributes_options.attr_id = 4) as color,
			(SELECT attr_options_name
				FROM $shop_db.products_variants INNER JOIN $main_db.eav_attributes_options ON $shop_db.products_variants.attr_value = $main_db.eav_attributes_options.id
				WHERE product_id = products.id AND $main_db.eav_attributes_options.attr_id = 5) as size,
			(SELECT attr_options_name
	 			FROM $shop_db.products_variants INNER JOIN $main_db.eav_attributes_options ON $shop_db.products_variants.attr_value = $main_db.eav_attributes_options.id
				WHERE product_id = products.id AND $main_db.eav_attributes_options.attr_id = 6) as shoe_size,
    		COALESCE(
    		    (SELECT attr_value FROM $shop_db.products_attributes WHERE attr_id = 36 AND products_attributes.product_id = products.parent_id),
				(SELECT attr_options_name FROM $shop_db.products_attributes pa
					INNER JOIN $main_db.eav_attributes_options ao ON pa.attr_value = ao.id
					INNER JOIN $main_db.eav_attributes a ON pa.attr_id = a.id
				WHERE
					product_id = products.parent_id
					AND a.attr_code = 'collection_name')
			) as season,
    		(SELECT attr_value FROM $shop_db.products_attributes WHERE attr_id = 39 AND products_attributes.product_id = products.parent_id AND attr_value IS NOT NULL LIMIT 1) as manufacturer_tariff,
    		(SELECT attr_value FROM shopinshop_shop1.products_attributes WHERE attr_id = 41 AND products_attributes.product_id = products.parent_id AND attr_value IS NOT NULL LIMIT 1) as country_of_origin

			FROM $shop_db.products as products
            LEFT JOIN $shop_db.products_inventory as pi ON pi.product_id = products.id
            LEFT JOIN $shop_db.products as parent ON products.parent_id = parent.id

            WHERE products.product_type != 'configurable' AND products.remove_flag = 0  and pi.available_qty > $min_available_qty ORDER BY `products`.`sku`
SQL;
        $products = $this->dbl->dbl_conn->rawQuery($query);

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

    public function configurableProduct($shopcode,$product_id)
    {
        $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
        $main_db = DB_NAME; //Constant variable
        $param = array($product_id);
        $query = "SELECT * FROM $shop_db.products WHERE $shop_db.products.id = ? ";

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

    public function attributDetails($shopcode,$product_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
  		$param = array($product_id,1);

  		$query = "SELECT prd_attr.attr_value, eav_attr.* FROM $shop_db.products_attributes as prd_attr  JOIN $main_db.eav_attributes as eav_attr ON eav_attr.id = prd_attr.attr_id WHERE prd_attr.product_id = ? AND eav_attr.status = ?";

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

    public function variantOptions($shopcode,$product_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id);

		$query = "SELECT eav_attr.attr_code, pv.attr_value, eav_attr_opt.attr_options_name FROM $shop_db.products_variants as pv INNER JOIN $main_db.eav_attributes_options as eav_attr_opt ON eav_attr_opt.id = pv.attr_value  JOIN   $main_db.eav_attributes as eav_attr ON eav_attr.id = pv.attr_id WHERE pv.product_id=? ";

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

	function check_attributes_options_exist_by_option_id($attr_id,$option_id){
		$main_db = DB_NAME; //Constant variable
  		$param = array($attr_id,$option_id);
		$query = "SELECT eav_attr_opt.attr_options_name FROM $main_db.eav_attributes_options as eav_attr_opt  WHERE eav_attr_opt.attr_id =? AND eav_attr_opt.id=? ";
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

}
