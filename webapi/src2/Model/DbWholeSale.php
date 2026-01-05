<?phpClass DbWholeSale{
	private $dbl;
	public function __construct()
	{		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}
    public function getInStockProducts($shopcode)
    {
        $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
        $main_db = DB_NAME; //Constant variable
        $query = "SELECT $shop_db.products.*,pi.available_qty FROM $shop_db.products  LEFT JOIN $shop_db.products_inventory as pi ON pi.product_id=$shop_db.products.id WHERE $shop_db.products.product_type != 'configurable' AND $shop_db.products.remove_flag = 0  and pi.available_qty > 0";
        $products = $this->dbl->dbl_conn->rawQuery($query);
      if ($this->dbl->dbl_conn->getLastErrno() === 0){
          if ($this->dbl->dbl_conn->count > 0){
              return $products;
          }else{              return false;
          }
      }else{
          return false;
      }    }
    public function configurableProduct($shopcode,$product_id)
    {        $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
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