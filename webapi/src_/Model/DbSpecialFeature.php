<?php
Class DbSpecialFeature{
	private $dbl;

	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}

	public function getproductscountsbycategoryid($shopcode,$category_id,$product_ids_str)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		//$main_db = DB_NAME; //Constant variable

  		$param = array($category_id,0);

  		$sub_query= "AND pc.product_id  IN (".$product_ids_str.") ";

  		$query = "SELECT COUNT(prod.id) as product_count from $shop_db.products as prod, $shop_db.products_category as pc where prod.id = pc.product_id
			AND pc.category_ids= ?
			AND prod.remove_flag=?
			$sub_query
			";

  		$product_count = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				// echo $this->dbl->dbl_conn->getLastQuery();
				return $product_count;

			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

  	public function getproductsbycategoryid($shopcode,$category_id,$product_ids_str)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		//$main_db = DB_NAME; //Constant variable
		$param = array($category_id,0);
		$sub_query= "AND pc.product_id  IN (".$product_ids_str.") ";
  		$query = "SELECT prod.product_type,pc.product_id  from $shop_db.products as prod, $shop_db.products_category as pc where prod.id = pc.product_id
			AND pc.category_ids= ?
			AND prod.remove_flag=?
			$sub_query ";

  		$product_data = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $product_data;
				// echo $this->dbl->dbl_conn->getLastQuery();
			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

	public function getAllCategories($shopcode,$shopid,$catlog_id,$product_ids_str)
  	{
  		$cart_obj = new DbCart();
		$common_obj = new DbCommonFeature();
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array(1,0);

  		$query = "SELECT main_cat.id,main_cat.cat_name,main_cat.cat_level,main_cat.slug FROM $main_db.category as main_cat INNER JOIN $shop_db.fbc_users_category_b2b as b2b ON main_cat.id=b2b.category_id  WHERE main_cat.status=? AND b2b.level=? ";


		$mainCatMenu = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->count > 0)
		{
			$final_arr = array();

			foreach($mainCatMenu as $cat)
			{
				// print_r($cat);
				$arr = array();
				$arr['id'] = $cat['id'];
				$arr['menu_name'] = $cat['cat_name'];
				$arr['menu_level'] = $cat['cat_level'];
				$arr['slug'] = $cat['slug'];
				$arr['category_id'] = $cat['id'];

				$product_count = $this->getproductscountsbycategoryid($shopcode,$arr['category_id'],$product_ids_str);
				$arr['product_count'] = $product_count[0]['product_count'];

				if($arr['product_count'] !=0 )
				{
					$product_data = $this->getproductsbycategoryid($shopcode,$arr['category_id'],$product_ids_str);
					$arr['product_data'] = $product_data;
				}
				$firstLevelCategory = $this->firstLevelCategory($shopcode,$cat['id']);
				$firstLevelCatePro = array();

				if($firstLevelCategory != false)
				{
					foreach($firstLevelCategory as $cat1)
					{
						$arr1 = array();
						$arr1['id'] = $cat1['id'];
						$arr1['menu_name'] = $cat1['cat_name'];
						$arr1['menu_level'] = $cat1['cat_level'];
						$arr1['slug'] = $cat1['slug'];
						$arr1['category_id'] = $cat1['id'];

						$product_count1 = $this->getproductscountsbycategoryid($shopcode,$arr1['category_id'],$product_ids_str);
						$arr1['product_count1'] = $product_count1[0]['product_count'];

						if($arr1['product_count1'] !=0 )
						{
							$product_data1 = $this->getproductsbycategoryid($shopcode,$arr1['category_id'],$product_ids_str);
							$arr1['product_data1'] = $product_data1;

							foreach($product_data1 as $product)
							{
								$final_product_data1 = array();

								$firstLevelCatePro[$product['product_id']] = $product['product_id'];

								$real_product_data1 = $this->getproductsbyid($shopcode,$product['product_id']);

								$collection_name = $this->getcollection_name($shopcode,$shopid,$product['product_id']);
								if(isset($collection_name) && $collection_name !='')
								{
									$real_product_data1['collection_name'] = $collection_name[0]['attr_options_name'];
								}else{
									$real_product_data1['collection_name']='';
								}
								$final_product_data1['real_product_data1'] = $real_product_data1;

								$table = 'catalog_builder_items';
								$flag = 'own';
								$where='';

								if($product['product_type'] == 'configurable'){
									$where = 'parent_id  = '.$product['product_id'].' AND catalog_builder_id ='.$catlog_id.'';
								}
								else{
									$where = 'product_id  = '.$product['product_id'].' AND catalog_builder_id ='.$catlog_id.'';
								}

								$catalog_builder_items  = $common_obj->getTableData($shopcode,$table,$flag,$where);
								$product_variants_Name_arr=array();
								$Variantid=$this->get_product_variant($shopcode,$product['product_id']);

										if(is_array($Variantid) && count($Variantid)>0  && $Variantid!=false){
											foreach($Variantid  as $value){
												$attr_id=$value['attr_id'];
												$AttrData=$cart_obj->getAttributeDetails($attr_id);
												if($AttrData==false){
													$attr_name='';
												}else{
													$attr_name=$AttrData['attr_name'];
												}
												if($attr_name!='' ){
													$product_variants_Name_arr[] = $attr_name;
												}
											}
										}

								$final_product_data1['VariantNameArr'] = $product_variants_Name_arr;


								if($product['product_type'] == 'simple'){
									$available_quantity= $this->getProductInventory($shopcode,$product['product_id']);
									$webshop_price= $this->getwebshopPriceById($shopcode,$product['product_id']);
									$catalog_builder_items[0]['webshop_price']= $webshop_price['webshop_price'];
									$catalog_builder_items[0]['available_quantity']= $available_quantity['available_qty'];
									$final_product_data1['catalog_builder_items'] = $catalog_builder_items[0];

								}else{

									$item_count = count($catalog_builder_items);
									for($i=0;$i<$item_count;$i++)
									{
										// print_r($catalog_builder_items[$i]);
										$product_data2=$this->getproductsbyid($shopcode,$catalog_builder_items[$i]['product_id']);



										$VariantInfo=$cart_obj->get_product_variant_details($shopcode,$catalog_builder_items[$i]['parent_id'],$catalog_builder_items[$i]['product_id']);

										$product_variants_arr=array();
										$product_variants_str='';

										if(is_array($VariantInfo) && count($VariantInfo)>0  && $VariantInfo!=false){
											foreach($VariantInfo  as $value){
												$attr_id=$value['attr_id'];
												$attr_value=$value['attr_value'];

												$AttrData=$cart_obj->getAttributeDetails($attr_id);

												if($AttrData==false){
													$attr_name='';
												}else{
													$attr_name=$AttrData['attr_name'];
												}


												$AttrOptionData=$cart_obj->getAttributeOptionDetails($attr_value);
												if($AttrOptionData==false){
													$attr_option_name='';
												}else{
													$attr_option_name=$AttrOptionData['attr_options_name'];
												}


												if($attr_name!='' && $attr_option_name!=''){
													$product_variants_arr[] = array($attr_name => $attr_option_name);
												}
											}
										}


										if(isset($product_variants_arr) && count($product_variants_arr)>0){
											$product_variants_str=json_encode($product_variants_arr);
										}else{
											$product_variants_str='';
										}

										$catalog_builder_items[$i]['product_variants'] = $product_variants_str;



										$webshop_price= $this->getwebshopPriceById($shopcode,$catalog_builder_items[$i]['product_id']);
										$catalog_builder_items[$i]['webshop_price']= $webshop_price['webshop_price'];

										$available_quantity= $this->getProductInventory($shopcode,$catalog_builder_items[$i]['product_id']);
										$product_data2[$i]['available_quantity']=$available_quantity['available_qty'];
										$catalog_builder_items[$i]['available_quantity'] = $available_quantity['available_qty'];

									}
									// print_r($catalog_builder_items);die();
									$final_product_data1['catalog_builder_items'] = $catalog_builder_items;
								}

								// print_r($final_product_data1);//die();
								$arr1['final_product_data1'][] = $final_product_data1;

							}

						}

						// print_r($arr1);
						$arr['menu_level_1'][] = $arr1;
					}

					// print_r($firstLevelCatePro); echo count($firstLevelCatePro);
				} else {
					if($arr['product_count'] !=0 )
					{

							foreach($product_data as $product)
							{
								$final_product_data1 = array();

								$real_product_data1 = $this->getproductsbyid($shopcode,$product['product_id']);

								$collection_name = $this->getcollection_name($shopcode,$shopid,$product['product_id']);
								if(isset($collection_name) && $collection_name !='')
								{
									$real_product_data1['collection_name'] = $collection_name[0]['attr_options_name'];
								}else{
									$real_product_data1['collection_name']='';
								}

								$style_code = $this->getstylecodeById($shopcode,$product['product_id']);
								if(isset($style_code) && $style_code !='')
								{
									$real_product_data1['style_code'] = $style_code[0]['attr_value'];
								}else{
									$real_product_data1['style_code']='';
								}

								$final_product_data1['real_product_data1'] = $real_product_data1;

								$table = 'catalog_builder_items';
								$flag = 'own';
								$where='';
								if($product['product_type'] == 'configurable'){
									$where = 'parent_id  = '.$product['product_id'].' AND catalog_builder_id ='.$catlog_id.'';
								}
								else{
									$where = 'product_id  = '.$product['product_id'].' AND catalog_builder_id ='.$catlog_id.'';
								}

								$catalog_builder_items  = $common_obj->getTableData($shopcode,$table,$flag,$where);

								$product_variants_Name_arr=array();
								$Variantid=$this->get_product_variant($shopcode,$product['product_id']);

										if(is_array($Variantid) && count($Variantid)>0  && $Variantid!=false){
											foreach($Variantid  as $value){
												$attr_id=$value['attr_id'];
												$AttrData=$cart_obj->getAttributeDetails($attr_id);
												if($AttrData==false){
													$attr_name='';
												}else{
													$attr_name=$AttrData['attr_name'];
												}
												if($attr_name!='' ){
													$product_variants_Name_arr[] = $attr_name;
												}
											}
										}

								$final_product_data1['VariantNameArr'] = $product_variants_Name_arr;

								if($product['product_type'] == 'simple'){
									$available_quantity= $this->getProductInventory($shopcode,$product['product_id']);
									$catalog_builder_items[0]['available_quantity']= $available_quantity['available_qty'];
									$webshop_price= $this->getwebshopPriceById($shopcode,$product['product_id']);
									$catalog_builder_items[0]['webshop_price']= $webshop_price['webshop_price'];
									$final_product_data1['catalog_builder_items'] = $catalog_builder_items[0];

								}else{

									$item_count = count($catalog_builder_items);
									for($i=0;$i<$item_count;$i++)
									{
										// print_r($catalog_builder_items[$i]);
										$product_data2=$this->getproductsbyid($shopcode,$catalog_builder_items[$i]['product_id']);

										$VariantInfo=$cart_obj->get_product_variant_details($shopcode,$catalog_builder_items[$i]['parent_id'],$catalog_builder_items[$i]['product_id']);

										$product_variants_arr=array();
										$product_variants_str='';

										if(is_array($VariantInfo) && count($VariantInfo)>0  && $VariantInfo!=false){
											foreach($VariantInfo  as $value){
												$attr_id=$value['attr_id'];
												$attr_value=$value['attr_value'];

												$AttrData=$cart_obj->getAttributeDetails($attr_id);

												if($AttrData==false){
													$attr_name='';
												}else{
													$attr_name=$AttrData['attr_name'];
												}


												$AttrOptionData=$cart_obj->getAttributeOptionDetails($attr_value);
												if($AttrOptionData==false){
													$attr_option_name='';
												}else{
													$attr_option_name=$AttrOptionData['attr_options_name'];
												}


												if($attr_name!='' && $attr_option_name!=''){
													$product_variants_arr[] = array($attr_name => $attr_option_name);
												}
											}
										}


										if(isset($product_variants_arr) && count($product_variants_arr)>0){
											$product_variants_str=json_encode($product_variants_arr);
										}else{
											$product_variants_str='';
										}

										$catalog_builder_items[$i]['product_variants'] = $product_variants_str;




										$webshop_price= $this->getwebshopPriceById($shopcode,$catalog_builder_items[$i]['product_id']);
										$catalog_builder_items[$i]['webshop_price']= $webshop_price['webshop_price'];


										$available_quantity= $this->getProductInventory($shopcode,$catalog_builder_items[$i]['product_id']);
										$product_data2[$i]['available_quantity']=$available_quantity['available_qty'];
										$catalog_builder_items[$i]['available_quantity'] = $available_quantity['available_qty'];

									}
									// print_r($catalog_builder_items);die();
									$final_product_data1['catalog_builder_items'] = $catalog_builder_items;
								}
								// print_r($final_product_data1);//die();
								$arr['final_product_data'][] = $final_product_data1;

							}

					}
				}

				$final_arr[] = $arr;
			}

			return $final_arr;
		}else{
			return false;
		}

  	}

  	public function firstLevelCategory($shopcode,$category_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array(1,1,$category_id);

  		$query = "SELECT cat_level1.id,cat_level1.cat_name,cat_level1.cat_level,cat_level1.slug FROM $main_db.category as cat_level1 INNER JOIN $shop_db.fbc_users_category_b2b as b2b ON cat_level1.id=b2b.category_id  WHERE cat_level1.status=? AND b2b.level=? AND cat_level1.parent_id=? ";

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

	public function  getproductsbyid($shopcode,$id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		//$main_db = DB_NAME; //Constant variable
		$param = array($id,0);

  		$query = "SELECT prod.id,prod.product_type,prod.name,prod.base_image,prod.product_code,prod.sku,prod.webshop_price from $shop_db.products as prod WHERE
			  prod.id=? AND prod.remove_flag=? ";

  		$product_data = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				 return $product_data;
				// echo $this->dbl->dbl_conn->getLastQuery();
			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

  	public function getProductInventory($shopcode,$product_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id);
		$query = "SELECT $shop_db.products_inventory.available_qty FROM $shop_db.products_inventory WHERE $shop_db.products_inventory.product_id = ?";
		$inventory = $this->dbl->dbl_conn->rawQueryOne($query,$param);

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

  	public function getProductVariantByProductId($shopcode,$product_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id);
		$query = "SELECT eav_attr.id, eav_attr.attr_code, eav_attr.attr_name FROM $shop_db.products_variants_master as pv_m INNER JOIN $main_db.eav_attributes as eav_attr ON eav_attr.id=pv_m.attr_id WHERE pv_m.product_id=? ORDER BY position ASC";

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

  	public function getstylecodeById($shopcode,$product_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id);

  		$query = "SELECT prd_attr.* FROM $shop_db.products_attributes as prd_attr, $main_db.eav_attributes as eav_attr WHERE eav_attr.attr_code='style_code' AND  prd_attr.product_id=?  AND prd_attr.attr_id=eav_attr.id;";

  		$attr_data = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $attr_data;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	public function getcollection_name($shopcode,$shopid,$product_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id,$shopid);
  		$query="SELECT attr_opt.attr_options_name FROM $shop_db.products_attributes as prd_attr, $main_db.eav_attributes_options as attr_opt ,$main_db.eav_attributes as eav_attr WHERE prd_attr.product_id=? AND eav_attr.attr_code='collection_name' AND eav_attr.shop_id=? AND prd_attr.attr_value=attr_opt.id AND prd_attr.attr_id=attr_opt.attr_id";
  		$attr_data = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $attr_data;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	public function getwebshopPriceById($shopcode,$product_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id);
		$query = "SELECT $shop_db.products.webshop_price FROM $shop_db.products WHERE $shop_db.products.id = ?";
		$webshop_price = $this->dbl->dbl_conn->rawQueryOne($query,$param);

  		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $webshop_price; // return result
			}else{
				return false;
			}

		}else{
			return false;
		}

  	}

  	 public function get_product_variant($shopcode,$parent_product_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		$params = array($parent_product_id);
		$result = $this->dbl->dbl_conn->rawQuery("SELECT * FROM $shop_db.products_variants_master where product_id = ?  ORDER BY id ASC",$params);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $result;
			}else{
				return false;
			}

		}else{
			return false;
		}

	}

	public function get_product_count($shopcode,$barcode)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$param = array($barcode,0);
  		$query = "SELECT COUNT(prod.id) as product_count from $shop_db.products as prod
			WHERE prod.barcode=? AND prod.remove_flag= ?";

  		$product_count = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				// echo $this->dbl->dbl_conn->getLastQuery();
				return $product_count;

			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

  	 public function get_product_data($shopcode,$barcode)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$params = array($barcode,0);
		$result = $this->dbl->dbl_conn->rawQuery("SELECT id,parent_id FROM $shop_db.products as prod where prod.barcode = ? AND prod.remove_flag= ? ",$params);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $result;
			}else{
				return false;
			}

		}else{
			return false;
		}

	}

	public function delete_catlog($shopcode,$catlog_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$params = array($catlog_id);
		$result = $this->dbl->dbl_conn->rawQuery("DELETE FROM $shop_db.catalog_builder where id = ? ",$params);
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


}
