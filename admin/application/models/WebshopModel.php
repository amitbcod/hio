<?php

class WebshopModel extends CI_Model

{

	public function __construct()

	{

		parent::__construct();

	}



	public function getOwener($ShopOwnerId)

	{

		$result = $this->db->get_where('fbc_users', array('fbc_user_id' => $ShopOwnerId))->row();

		return $result;

	}



	/* public function getShopData($ShopOwnerId,$shopid)

	{



		$result = $this->db->get_where('fbc_users_shop', array('fbc_user_id' => $ShopOwnerId,'shop_id'=>$shopid))->row();

		return $result;

	} */



	public function getThemesData()

	{

		$shop_id = $this->session->userdata('ShopID');

		$wherarry = array('status' => 1);



		if ($shop_id != 1) {

			$wherarry = array('status' => 1, 'id !=' => 5);

		}



		$result = $this->db->get_where('themes_master', $wherarry)->result();

		return $result;

	}



	public function getThemeData($id)

	{

		$result = $this->db->get_where('themes_master', array('id' => $id,))->row();

		return $result;

	}



	public function getActiveTheme()

	{

		$result = $this->db->get_where('themes_webshops', array('current_theme' => 1,))->row();

		return $result;

	}



	public function getSalesruleCoupon($rules_id, $email_address)

	{

		$result = $this->db->get_where('salesrule_coupon', array('rule_id' => $rules_id, 'email_address' => $email_address))->row();

		return $result;

	}



	// Menus Data

	public function getMenuType($id)

	{

		$result = $this->db->get_where('static_blocks', array('id' => $id,))->row();

		return $result;

	}



	public function getCatMenus($id)

	{

		$result = $this->db->get_where('webshop_cat_menus', array('static_block_id' => $id,))->result();

		return $result;

	}



	public function getCustomMenus($blockID)

	{

		$this->db->select('wcm.*');

		$this->db->from('webshop_custom_menus wcm');

		$this->db->where('wcm.static_block_id', $blockID);

		$this->db->where('wcm.menu_level', 0);

		$this->db->order_by('wcm.position, wcm.id');



		$result = $this->db->get();

		if ($result->num_rows() > 0) {

			$final_arr = array();



			$browseByMenu = $result->result_array();

			foreach ($browseByMenu as $menu) {

				$firstLevelMenu = $this->firstLevelMenu($menu['id'], $blockID);

				if ($firstLevelMenu != false) {

					foreach ($firstLevelMenu as $menu1) {

						$secondLevelMenu = $this->secondLevelMenu($menu1['id'], $menu['id'], $blockID);

						if ($secondLevelMenu != false) {

							foreach ($secondLevelMenu as $menu2) {



								$thirdLevelMenu = $this->thirdLevelMenu($menu2['id'], $menu['id'], $blockID);

								if ($thirdLevelMenu != false) {

									foreach ($thirdLevelMenu as $menu3) {

										$arr3['id'] = $menu3['id'];

										$arr3['menu_name'] = $menu3['menu_name'];

										$arr3['menu_level'] = $menu3['menu_level'];

										$arr3['position'] = $menu3['position'];

										$arr3['category_id'] = isset($menu3['category_id']) ? $menu3['category_id'] : '';



										$menu2['menu_level_3'][] = $arr3;

									}

								}



								$menu1['menu_level_2'][] = $menu2;

							}

						}



						$menu['menu_level_1'][] = $menu1;

					}

				}



				$final_arr[] = $menu;

			}



			return $final_arr;

		} else {

			return false;

		}

	}

	// End

	public function insertData($table, $data)

	{

		$this->db->insert($table, $data);

		if ($this->db->affected_rows() > 0) {

			$last_insert_id = $this->db->insert_id();

			return $last_insert_id;

		} else {

			return false;

		}

	}



	public function insertDBdata($table, $data)

	{

		$this->db->insert($table, $data);

		if ($this->db->affected_rows() > 0) {

			$last_insert_id = $this->db->insert_id();

			return $last_insert_id;

		} else {

			return false;

		}

	}



	public function updateData($tableName, $updateData)

	{



		$this->db->update($tableName, $updateData);

		if ($this->db->affected_rows() > 0) {

			return true;

		} else {

			return false;

		}

	}



	public function updateNewData($tableName, $condition, $updateData)

	{

		$this->db->where($condition);

		$this->db->update($tableName, $updateData);

		if ($this->db->affected_rows() > 0) {

			return true;

		} else {

			return false;

		}

	}



	public function getIdentifier($identifier, $pId)

	{

		$result = $this->db->query("

    		SELECT * FROM `cms_pages` WHERE

    		remove_flag = 0 AND

			identifier = '" . $identifier . "'

			AND id <> " . $pId . ";");

		return $result->result();

	}



	public function getPages()

	{

		$result = $this->db->query("

    		SELECT * FROM `cms_pages` WHERE remove_flag = 0 ;");

		return $result->result();

	}



	public function getPageDataId($id)

	{

		$result = $this->db->query("

    		SELECT * FROM `cms_pages` WHERE remove_flag = 0 AND id = " . $id . ";");

		return $result->row();

	}





	public function getBlockIdentifier($identifier, $bId)

	{

		$result = $this->db->query("

    		SELECT * FROM `static_blocks` WHERE

			identifier = '" . $identifier . "'

			AND id <> " . $bId . ";");

		return $result->result();

	}



	public function getStaticBlocks()

	{

		$result = $this->db->query("SELECT * FROM `static_blocks` ORDER BY coalesce(updated_at, created_at) DESC ;");

		// echo $this->db->last_query();

		return $result->result();

	}



	public function getSingleDataByID($tableName, $condition, $select)

	{

		if (!empty($select)) {

			$this->db->select($select);

		}

		$this->db->where($condition);

		$query = $this->db->get($tableName);

		return $query->row();

	}



	public function deleteData($tableName, $condition)

	{

		$this->db->where($condition);

		$this->db->delete($tableName);

		if ($this->db->affected_rows() > 0) {

			return true;

		} else {

			return false;

		}

	}



	public function deleteCustomMenu($blockID)

	{

		$this->db->where('id', $blockID);

		$this->db->or_where('menu_main_parent_id', $blockID);

		$this->db->delete('webshop_custom_menus');

		if ($this->db->affected_rows() > 0) {

			return true;

		} else {

			return false;

		}

	}



	public function getWhere($tableName, $condition)

	{

		$result = $this->db->get_where($tableName, $condition)->result();

		return $result;

	}



	public function getBanners($blockID)

	{

		$this->db->select('*');

		$this->db->from('banners');

		$this->db->where('static_block_id', $blockID);

		$result = $this->db->get();

		/*$result =$this->db->query("

    		SELECT * FROM `banners` where static_block_id=;");*/

		return $result->result();

	}



	public function get_payementgateways($shop_country)

	{

		$main_db_name = $this->db->database;



		$this->db->select('pm.id,pm.payment_gateway, wp.integrate_with_ws, wp.payment_id, pm.payment_type, pm.payment_gateway_key, if(wp.payment_type_details != "",wp.payment_type_details,pm.payment_type_details) as payment_type_details, if(wp.gateway_details != "",wp.gateway_details,pm.gateway_details) as gateway_details, wp.status ');

		$this->db->from($main_db_name . '.payment_master pm');

		$this->db->where('pm.country', $shop_country);

		//$this->db->or_where('pm.country', 'MU');

		$this->db->join('webshop_payments wp', 'pm.id = wp.payment_id', 'Left');

		$result = $this->db->get();

		// echo $this->db->last_query();

		if ($result->num_rows() > 0) {

			return $result->result_array();

		} else {

			return false;

		}

	}





	public function get_gateway_details($id)

	{

		$main_db_name = $this->db->database;



		$this->db->select('*');

		$this->db->from($main_db_name . '.payment_master pm');

		$this->db->where('pm.id', $id);

		$result = $this->db->get();

		//echo $this->db->last_query();

		if ($result->num_rows() > 0) {

			return $result->row_array();

		} else {

			return false;

		}

	}



	public function shop_gateway_credentials($id)

	{

		$result = $this->db->query("SELECT * FROM `webshop_payments` WHERE payment_id = " . $id);

		//return $result->row();

		if ($result->num_rows() > 0) {

			return $result->row_array();

		} else {

			return false;

		}

	}



	public function check_existing_id($id)

	{

		$this->db->where('payment_id', $id);

		$query = $this->db->get('webshop_payments');

		// echo $this->db->last_query();die();

		if ($query->num_rows() > 0) {

			return true;

		} else {

			return false;

		}

	}



	public function  insert_shop_gateway($insert_array)

	{

		$query = $this->db->insert('webshop_payments', $insert_array);

		if ($query) {

			return true;

		}

	}



	public function update_shop_gateway($update_array, $id)

	{

		$this->db->where('payment_id', $id);

		$query = $this->db->update('webshop_payments', $update_array);

		if ($query) {

			return true;

		}

	}



	public function update_integration($id, $integrate_with_ws)

	{

		$this->db->where('payment_id', $id);

		$this->db->set('integrate_with_ws', $integrate_with_ws);

		$query = $this->db->update('webshop_payments');

		if ($query) {

			return true;

		}

	}



	public function getProductBlocks()

	{

		$result = $this->db->query("SELECT * FROM `products_block_master`");

		return $result->result();

	}



	public function getProductBlockList($identifier)

	{

		$date = strtotime(date('d-m-Y'));

		$sub_query = '';

		if ($identifier != 'prelauch') {

			$sub_query = 'status = 1 AND launch_date <= ' . $date . ' AND';

		}



		//$result =$this->db->query("SELECT id,name,product_code,product_type,launch_date,status FROM `products` WHERE status = 1 AND launch_date <= CURRENT_DATE AND product_type = 'simple' OR product_type = 'configurable'");

		$result = $this->db->query("SELECT id,name,product_code,product_type,launch_date,status FROM `products` WHERE remove_flag = 0 AND $sub_query (product_type = 'simple' OR product_type = 'configurable')");

		return $result->result();

	}



	public function getProductBlocksDetailsById($id)

	{

		$result = $this->db->query("SELECT * FROM products_block_details WHERE pb_master_id = " . $id);

		return $result->result();

	}



	public function getSelectedProductBlocks($id)

	{

		$result = $this->db->query("SELECT * FROM products_block_details WHERE pb_master_id = " . $id);

		return $result->row();

	}



	public function getContactUsRequests()

	{

		$result = $this->db->query("SELECT * FROM contact_us order by created_at DESC");

		return $result->result();

	}



	public function get_viewContactUsMessage($id)

	{

		$result = $this->db->query("SELECT contact_us.* /*,multi_languages.display_name*/ FROM contact_us /*LEFT JOIN multi_languages ON contact_us.communication_language=multi_languages.code*/ WHERE contact_us.id = " . $id);

		return $result->row();

	}



	public function getAllMenus($blockID, $menuID = '')

	{

		$this->db->select('wcm.*');

		$this->db->from('webshop_custom_menus wcm');

		$this->db->where('wcm.static_block_id', $blockID);

		$this->db->where('wcm.menu_level', 0);

		if ($menuID != '') {

			$this->db->where_not_in('wcm.id', $menuID);

		}



		$result = $this->db->get();

		// echo $this->db->last_query();die();

		if ($result->num_rows() > 0) {

			$final_arr = array();



			$browseByMenu = $result->result_array();

			foreach ($browseByMenu as $menu) {

				$firstLevelMenu = $this->firstLevelMenu($menu['id'], $blockID, $menuID);

				if ($firstLevelMenu != false) {

					foreach ($firstLevelMenu as $menu1) {

						$secondLevelMenu = $this->secondLevelMenu($menu1['id'], $menu['id'], $blockID, $menuID);

						if ($secondLevelMenu != false) {

							foreach ($secondLevelMenu as $menu2) {



								$menu1['menu_level_2'][] = $menu2;

							}

						}



						$menu['menu_level_1'][] = $menu1;

					}

				}



				$final_arr[] = $menu;

			}

			return $final_arr;

		} else {

			return false;

		}

	}



	public function firstLevelMenu($menu_id, $blockID, $exist_menu_Id = '')

	{

		$this->db->select('wcm.*');

		$this->db->from('webshop_custom_menus wcm');

		$this->db->where('wcm.static_block_id', $blockID);

		$this->db->where('wcm.menu_level', 1);

		$this->db->where('wcm.menu_parent_id', $menu_id);

		if ($exist_menu_Id != '') {

			$this->db->where_not_in('wcm.id', $exist_menu_Id);

		}

		$this->db->order_by('wcm.position, wcm.id');



		$result = $this->db->get();

		if ($result->num_rows() > 0) {

			return $result->result_array();

		} else {

			return false;

		}

	}



	public function secondLevelMenu($menu_parent_id, $menu_main_parent_id, $blockID, $exist_menu_Id = '')

	{

		$this->db->select('wcm.*');

		$this->db->from('webshop_custom_menus wcm');

		$this->db->where('wcm.static_block_id', $blockID);

		$this->db->where('wcm.menu_level', 2);

		$this->db->where('wcm.menu_parent_id', $menu_parent_id);

		$this->db->where('wcm.menu_main_parent_id', $menu_main_parent_id);

		if ($exist_menu_Id != '') {

			$this->db->where_not_in('wcm.id', $exist_menu_Id);

		}

		$this->db->order_by('wcm.position, wcm.id');



		$result = $this->db->get();

		if ($result->num_rows() > 0) {

			return $result->result_array();

		} else {

			return false;

		}

	}



	public function thirdLevelMenu($menu_parent_id, $menu_main_parent_id, $blockID, $exist_menu_Id = '')

	{

		$this->db->select('wcm.*');

		$this->db->from('webshop_custom_menus wcm');

		$this->db->where('wcm.static_block_id', $blockID);

		$this->db->where('wcm.menu_level', 3);

		$this->db->where('wcm.menu_parent_id', $menu_parent_id);

		$this->db->where('wcm.menu_main_parent_id', $menu_main_parent_id);

		if ($exist_menu_Id != '') {

			$this->db->where_not_in('wcm.id', $exist_menu_Id);

		}

		$this->db->order_by('wcm.position, wcm.id');



		$result = $this->db->get();

		if ($result->num_rows() > 0) {

			return $result->result_array();

		} else {

			return false;

		}

	}



	public function getAllCategories($blockID = '')

	{

		$main_db_name = $this->db->database;



		$wcm_catid = '';

		if ($blockID != '') {

			$wcm_catid = ',wcm.category_id,wcm.position';

		}



		$this->db->select('main_cat.id,main_cat.cat_name,main_cat.cat_level' . $wcm_catid);

		$this->db->from($main_db_name . '.category main_cat');

		$this->db->where('main_cat.status', 1);

		$this->db->where('main_cat.cat_level', 0);

		// $this->db->join('fbc_users_category_b2b b2b','main_cat.id = b2b.category_id','INNER');

		if ($blockID != '') {

			$this->db->join('webshop_cat_menus wcm', 'main_cat.id = wcm.category_id AND wcm.static_block_id = ' . $blockID, 'Left');

		}



		$result = $this->db->get();

		//echo $this->db->last_query();die();



		if ($result->num_rows() > 0) {

			$final_arr = array();



			$browseByCategory = $result->result_array();

			// echo '<pre>';print_r($browseByCategory);exit;





			foreach ($browseByCategory as $cat) {

				$firstLevelCategory = $this->firstLevelCategory($cat['id'], $blockID);

				if ($firstLevelCategory != false) {

					foreach ($firstLevelCategory as $cat1) {

						$secondLevelCategory = $this->secondLevelCategory($cat1['id'], $cat['id'], $blockID);

						if ($secondLevelCategory != false) {

							foreach ($secondLevelCategory as $cat2) {

								$arr2['id'] = $cat2['id'];

								$arr2['cat_name'] = $cat2['cat_name'];

								$arr2['cat_level'] = $cat2['cat_level'];

								if ($blockID != '') {

									$arr2['position'] = $cat2['position'];

								}

								$arr2['category_id'] = isset($cat2['category_id']) ? $cat2['category_id'] : '';

								$cat1['cat_level_2'][] = $arr2;

							}

						}



						$cat['cat_level_1'][] = $cat1;

					}

				}



				$final_arr[] = $cat;

			}

			return $final_arr;

		} else {

			return false;

		}

	}



	public function getAllCategories_Exceptional($blockID = '')

	{

		$main_db_name = $this->db->database;



		$wcm_catid = '';

		if ($blockID != '') {

			$wcm_catid = ',ets.category_id';

		}



		$this->db->select('main_cat.id,main_cat.cat_name,main_cat.cat_level' . $wcm_catid);

		$this->db->from($main_db_name . '.category main_cat');

		$this->db->where('main_cat.status', 1);

		$this->db->where('main_cat.cat_level', 0);

		if ($blockID != '') {

			$this->db->join('exceptional_taxes_set_details ets', 'main_cat.id = ets.category_id AND ets.exc_taxes_id = ' . $blockID, 'Left');

		}



		$result = $this->db->get();

		// echo $this->db->last_query();die();



		if ($result->num_rows() > 0) {

			$final_arr = array();



			$browseByCategory = $result->result_array();



			foreach ($browseByCategory as $cat) {

				$firstLevelCategory = $this->firstLeveExceptionalCategory($cat['id'], $blockID);

				if ($firstLevelCategory != false) {

					foreach ($firstLevelCategory as $cat1) {

						$secondLevelCategory = $this->secondLevelExceptionlCategory($cat1['id'], $cat['id'], $blockID);

						if ($secondLevelCategory != false) {

							foreach ($secondLevelCategory as $cat2) {

								$arr2['id'] = $cat2['id'];

								$arr2['cat_name'] = $cat2['cat_name'];

								$arr2['cat_level'] = $cat2['cat_level'];

								$arr2['category_id'] = isset($cat2['category_id']) ? $cat2['category_id'] : '';

								$cat1['cat_level_2'][] = $arr2;

							}

						}



						$cat['cat_level_1'][] = $cat1;

					}

				}



				$final_arr[] = $cat;

			}



			return $final_arr;

		} else {

			return false;

		}

	}





	public function firstLeveExceptionalCategory($category_id, $blockID = '')

	{

		$main_db_name = $this->db->database;



		$wcm_catid = '';

		if ($blockID != '') {

			$wcm_catid = ',ets.category_id';

		}



		$this->db->select('cat_level1.id,cat_level1.cat_name,cat_level1.cat_level' . $wcm_catid);

		$this->db->from($main_db_name . '.category cat_level1');

		$this->db->where('cat_level1.status', 1);

		$this->db->where('cat_level1.parent_id', $category_id);

		$this->db->where('cat_level1.cat_level', 1);

		//$this->db->join('fbc_users_category_b2b b2b','cat_level1.id = b2b.category_id','INNER');

		if ($blockID != '') {

			$this->db->join('exceptional_taxes_set_details ets', 'cat_level1.id = ets.category_id AND ets.exc_taxes_id = ' . $blockID, 'Left');

		}



		$result = $this->db->get();

		if ($result->num_rows() > 0) {

			return $result->result_array();

		} else {

			return false;

		}

	}



	public function secondLevelExceptionlCategory($cat_parent_id, $cat_main_parent_id, $blockID = '')

	{

		$main_db_name = $this->db->database;



		$wcm_catid = '';

		if ($blockID != '') {

			$wcm_catid = ',ets.category_id';

		}



		$this->db->select('cat_level2.id,cat_level2.cat_name,cat_level2.cat_level' . $wcm_catid);

		$this->db->from($main_db_name . '.category cat_level2');

		$this->db->where('cat_level2.status', 1);

		$this->db->where('cat_level2.parent_id', $cat_parent_id);

		$this->db->where('cat_level2.main_parent_id', $cat_main_parent_id);

		$this->db->where('cat_level2.cat_level', 2);

		//	$this->db->join('fbc_users_category_b2b b2b','cat_level2.id = b2b.category_id','INNER');

		if ($blockID != '') {

			$this->db->join('exceptional_taxes_set_details ets', 'cat_level2.id = ets.category_id AND ets.exc_taxes_id = ' . $blockID, 'Left');

		}



		$result = $this->db->get();

		if ($result->num_rows() > 0) {

			return $result->result_array();

		} else {

			return false;

		}

	}



	public function firstLevelCategory($category_id, $blockID = '')

	{

		$main_db_name = $this->db->database;



		$wcm_catid = '';

		if ($blockID != '') {

			$wcm_catid = ',wcm.category_id,wcm.position';

		}



		$this->db->select('cat_level1.id,cat_level1.cat_name,cat_level1.cat_level' . $wcm_catid);

		$this->db->from($main_db_name . '.category cat_level1');

		$this->db->where('cat_level1.status', 1);

		$this->db->where('cat_level1.parent_id', $category_id);

		$this->db->where('cat_level1.cat_level', 1);

		//$this->db->join('fbc_users_category_b2b b2b','cat_level1.id = b2b.category_id','INNER');

		if ($blockID != '') {

			$this->db->join('webshop_cat_menus wcm', 'cat_level1.id = wcm.category_id AND wcm.static_block_id = ' . $blockID, 'Left');

		}



		$result = $this->db->get();

		if ($result->num_rows() > 0) {

			return $result->result_array();

		} else {

			return false;

		}

	}



	public function secondLevelCategory($cat_parent_id, $cat_main_parent_id, $blockID = '')

	{

		$main_db_name = $this->db->database;



		$wcm_catid = '';

		if ($blockID != '') {

			$wcm_catid = ',wcm.category_id,wcm.position';

		}



		$this->db->select('cat_level2.id,cat_level2.cat_name,cat_level2.cat_level' . $wcm_catid);

		$this->db->from($main_db_name . '.category cat_level2');

		$this->db->where('cat_level2.status', 1);

		$this->db->where('cat_level2.parent_id', $cat_parent_id);

		$this->db->where('cat_level2.main_parent_id', $cat_main_parent_id);

		$this->db->where('cat_level2.cat_level', 2);

		//$this->db->join('fbc_users_category_b2b b2b','cat_level2.id = b2b.category_id','INNER');

		if ($blockID != '') {

			$this->db->join('webshop_cat_menus wcm', 'cat_level2.id = wcm.category_id AND wcm.static_block_id = ' . $blockID, 'Left');

		}



		$result = $this->db->get();

		if ($result->num_rows() > 0) {

			return $result->result_array();

		} else {

			return false;

		}

	}



	//Start Webshop Discount

	public function getCustomerTypeMaster()

	{

		$result = $this->db->query("SELECT * FROM customers_type_master");

		return $result->result();

	}



	public function getB2BCatDetails()

	{

		$main_db_name = $this->db->database;



		$this->db->distinct();

		$this->db->select('cat.*, IF(cat.parent_id <= 0, cat.cat_name, cg2.cat_name) AS cat_name, cat.parent_id,IF(cat.parent_id >0, cg1.cat_name,"-") as  sub_cat_name');

		$this->db->from('category as cat');

		$this->db->join($main_db_name . '.category as cg1', 'cg1.id = cat.id', 'LEFT');

		$this->db->join($main_db_name . '.category as cg2', 'cg2.id = cat.parent_id', 'LEFT');

		$this->db->join('products_category as pc', 'pc.category_ids = cat.id', 'INNER');



		$this->db->where('(cat.cat_level IN (0,1))');

		$this->db->order_by('cat.id ASC,cat.parent_id ASC');



		$query = $this->db->get();

		$result = $query->result();



		$finalResult = array();

		if (is_array($result) && count($result) > 0) {

			foreach ($result as $key => $value) {

				$productCount = $this->getProductCountByCat($value->id);

				if ($productCount > 0) {

					$finalResult[] = (object)array('category_id' => $value->id, 'cat_name' => $value->cat_name, 'sub_cat_name' => $value->sub_cat_name, 'level' => $value->cat_level, 'parent_id' => $value->parent_id, 'product_count' => $productCount);

				}

			}

		}



		return $finalResult;

	}



	public function getProductCountByCat($cat_id)

	{

		$this->db->select('p.*');

		$this->db->from('products as p');

		$this->db->join('products_category as pc', 'p.id=pc.product_id', 'INNER');

		$this->db->where(array('pc.category_ids' => $cat_id, 'p.remove_flag' => 0));

		$query = $this->db->get();

		//print_r($this->db->last_query());

		return $query->num_rows();

	}



	public function getCatalogueDiscountList($type)

	{

		$this->db->select('sr.*, src.coupon_id, src.coupon_code');

		$this->db->from('salesrule as sr');

		$this->db->join('salesrule_coupon as src', 'src.rule_id = sr.rule_id', 'INNER');

		$this->db->where(array('sr.type' => $type, 'sr.remove_flag' => 0));

		if ($type == 4) {

			$this->db->group_by('sr.rule_id');

		}

		$this->db->order_by('sr.start_date', 'DESC');

		$query = $this->db->get();

		$result = $query->result();



		return $result;

	}



	public function getDiscountDetailsById($id)

	{

		$this->db->select('sr.*, src.coupon_id, src.coupon_code,src.coupon_code_prefix, GROUP_CONCAT(src.email_address) as email_ids');

		$this->db->from('salesrule as sr');

		$this->db->join('salesrule_coupon as src', 'src.rule_id = sr.rule_id', 'INNER');

		$this->db->where(array('sr.rule_id' => $id, 'sr.remove_flag' => 0));

		$query = $this->db->get();

		$result = $query->row();



		return $result;

	}



	public function getMultiDataById($tableName, $condition, $select, $order_by_column = '', $order_by_type = '')

	{

		if (!empty($select)) {

			$this->db->select($select);

		}

		$this->db->where($condition);



		if (isset($order_by_column) &&  $order_by_column != '') {

			$this->db->order_by($order_by_column, $order_by_type);

		}



		$query = $this->db->get($tableName);

		return $query->result();

	}



	public function getProductDetailsByCatId($cat_id)

	{

		$main_db_name = $this->db->database;



		$this->db->select('p.*, pc.category_ids as category_id, cat.cat_name');

		$this->db->from('products as p');

		$this->db->join('products_category as pc', 'p.id=pc.product_id', 'INNER');

		$this->db->join($main_db_name . '.category as cat', 'cat.id = pc.category_ids', 'INNER');

		$this->db->where(array('pc.category_ids' => $cat_id, 'p.remove_flag' => 0));

		// $this->db->where_in('p.product_type' , array('simple','conf-simple'));

		$query = $this->db->get();

		$result = $query->result();



		return $result;

	}



	public function getConfigChildProduct($product_id)

	{

		$this->db->select('p.id, p.parent_id, p.name, p.sku, p.barcode, p.webshop_price, inv.qty');

		$this->db->from('products as p');

		$this->db->join('products_inventory as inv', 'p.id=inv.product_id', 'INNER');

		$this->db->where(array('p.parent_id' => $product_id, 'p.remove_flag' => 0));

		$query = $this->db->get();

		$result = $query->result_array();



		return $result;

	}



	public function getVariantsMultiple($product_id)

	{

		$main_db_name = $this->db->database;

		$product_ids_string = implode(',', $product_id);

		$query = "SELECT ev.attr_name, evp.attr_options_name, pv.product_id FROM products_variants pv

		INNER JOIN $main_db_name.eav_attributes ev ON ev.id=pv.attr_id

		INNER JOIN $main_db_name.eav_attributes_options evp ON evp.id=pv.attr_value AND ev.id = evp.attr_id WHERE pv.product_id IN($product_ids_string)";

		$query = $this->db->query($query);

		$result = $query->result();

		return $result;

	}



	public function getVariants($product_id)

	{

		$main_db_name = $this->db->database;



		$this->db->select('ev.attr_name, evp.attr_options_name');

		$this->db->from('products_variants pv');

		$this->db->join($main_db_name . '.eav_attributes ev', 'ev.id=pv.attr_id', 'INNER');

		$this->db->join($main_db_name . '.eav_attributes_options evp', 'evp.id=pv.attr_value AND ev.id = evp.attr_id', 'INNER');

		$this->db->where(array('pv.product_id' => $product_id));

		$query = $this->db->get();

		$result = $query->result_array();



		return $result;

	}



	public function getApplyOnCatByRuleId($rule_Id)

	{

		$this->db->select('sr.apply_on_categories, sr.apply_on_products');

		$this->db->from('salesrule as sr');

		$this->db->where(array('sr.rule_id' => $rule_Id, 'sr.remove_flag' => 0));

		$query = $this->db->get();

		$result = $query->row();



		return $result;

	}



	public function getConfigChildProductIds($product_id)

	{

		$this->db->select('GROUP_CONCAT(p.id) as conf_simple_ids, p.parent_id');

		$this->db->from('products as p');

		$this->db->where(array('p.parent_id' => $product_id, 'p.remove_flag' => 0));



		$this->db->group_by('p.parent_id');



		$query = $this->db->get();

		$result = $query->row();



		return $result;

	}



	public function getAllSimpleProductList()

	{

		$main_db_name = $this->db->database;



		$this->db->select('p.*, inv.qty, IF(p.shop_id >0, fus.org_shop_name,"-") as org_shop_name');

		$this->db->from('products as p');

		$this->db->join('products_inventory as inv', 'inv.product_id=p.id', 'LEFT');

		$this->db->join($main_db_name . '.fbc_users_shop as fus', 'p.shop_id = fus.shop_id', 'LEFT');

		$this->db->where(array('p.remove_flag' => 0));

		$this->db->where_in('p.product_type', array('simple', 'conf-simple'));

		$query = $this->db->get();

		$result = $query->result();



		return $result;

	}



	public function getAllProductsData()

	{

		$this->db->select('p.*');

		$this->db->from('products as p');

		// $this->db->where('p.status',1); //18-7-21

		$this->db->where(array('p.remove_flag' => 0));

		$this->db->where_in('p.product_type', array('simple', 'conf-simple'));

		$query = $this->db->get();

		$result = $query->result();

		return $result;

	}



	public function get_datatables_special_prices()

	{

		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';

		$this->get_datatables_Allproducts_special_prices($term);

		if ($_REQUEST['length'] != -1)

			$this->db->limit($_REQUEST['length'], $_REQUEST['start']);

		$query = $this->db->get();

		return $query->result();

	}



	public function get_datatables_Allproducts_special_prices($term = '')

	{

		$column = array('', 'p.sku', 'p.name', '', 'p.webshop_price', '', 'psp.special_price', 'psp.special_price_from', 'psp.special_price_to', '', '');

		$this->db->select('psp.*,p.sku,p.name,p.webshop_price');

		$this->db->from('products_special_prices as psp');

		$this->db->join('products as p', 'p.id=psp.product_id');

		// $this->db->order_by('psp.special_price_from','DESC');

		$this->db->where(array('p.remove_flag' => 0)); //18-7-21

		// $this->db->where(array('p.status'=>0)); //18-7-21



		if ($term != '') {

			$this->db->where(" (

				  p.sku LIKE '%$term%'

				  OR p.name LIKE '%$term%'

				  OR p.webshop_price LIKE '%$term%'



				   )");

		}



		if (isset($_REQUEST['order'])) // here order processing

		{

			$this->db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);

		} else {

			$this->db->order_by('psp.special_price_from', 'DESC');

		}

	}



	public function countfilterspecialprice()

	{

		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';

		$this->get_datatables_Allproducts_special_prices($term);

		$query = $this->db->get();

		return $query->num_rows();

	}



	public function countspecialpricerecord()

	{

		$this->db->select('psp.*');

		$this->db->from('products_special_prices as psp');

		$this->db->join('products as p', 'p.id=psp.product_id');

		$this->db->where(array('p.remove_flag' => 0));

		$query = $this->db->count_all_results();

		return $query;

	}



	public function getAllproducts_special_prices()

	{

		$this->db->select('psp.*,p.sku,p.name,p.webshop_price');

		$this->db->from('products_special_prices as psp');

		$this->db->join('products as p', 'p.id=psp.product_id');

		$this->db->order_by('psp.special_price_from', 'DESC');

		$this->db->where(array('p.remove_flag' => 0)); //18-7-21

		// $this->db->where(array('p.status'=>0)); //18-7-21

		$query = $this->db->get();

		$result = $query->result();

		return $result;

	}



	public function getspecailpricingForCSVImport()

	{

		$this->db->select('

			`p`.`sku`,

			`psp`.special_price,

			`psp`.special_price_from,

			`psp`.special_price_to,

			`psp`.display_original,

			`p`.`product_type`

		');

		$this->db->from('products_special_prices as psp');

		$this->db->join('products as p', 'p.id=psp.product_id');

		$this->db->join('products as parent', 'p.parent_id = parent.id', 'left');

		$this->db->where(array('p.remove_flag' => 0));  //05-07-21

		$this->db->where('(parent.remove_flag IS NULL OR parent.remove_flag = 0)');

		$query = $this->db->get();

		// echo $this->db->last_query();die();

		$result = $query->result();

		return $result;

	}



	public function getspecailpricingForALLCSVImport()

	{

		$main_db_name = $this->db->database;

		$sql = "SELECT GROUP_CONCAT(c.cat_name separator ',') as cat_name, p.launch_date,p.id,p.parent_id,p.sku,p.barcode,p.name,p.cost_price,p.tax_percent,p.price,p.webshop_price,pinv.available_qty

				FROM products as p

				JOIN products_inventory as pinv ON pinv.product_id=p.id

				LEFT JOIN products_category as pc ON pc.product_id=p.id

				LEFT JOIN $main_db_name.category as c ON c.id=pc.category_ids

				WHERE p.remove_flag = 0

				AND (p.parent_id is NOT NULl OR p.parent_id !=0)

				AND (c.cat_level is NULl OR c.cat_level =0)

				AND p.product_type IN('simple', 'conf-simple')

				GROUP BY p.id";



		$query = $this->db->query($sql);

		$result = $query->result();

		return $result;

	}



	public function getlaunchDatebyId($id)

	{

		$this->db->select('launch_date');

		$this->db->where('id', $id);

		$query = $this->db->get('products');

		$resultArr = $query->row();

		// print_r($resultArr->launch_date);die();

		if (isset($resultArr->launch_date)) {

			return $resultArr->launch_date;

		} else {

			return false;

		}

	}



	public function check_product_exists_by_sku($sku)

	{

		$this->db->select('*');

		// $this->db->where('status',1);

		$this->db->where('sku', $sku);

		$this->db->where('remove_flag', 0);  //05-07-21

		$query = $this->db->get('products');

		$resultArr = $query->row_array();

		//echo $this->db->last_query();

		return $resultArr;

	}



	public function check_all_skus_exist(array $skus): bool

	{

		$result = (int) $this->db->query('SELECT COUNT(*) as sku_count FROM products WHERE remove_flag = 0 AND sku IN ?', [$skus])->row()->sku_count;



		return $result === count($skus);

	}



	public function check_which_skus_do_not_exist(array $skus): array

	{

		$skus = array_map(function ($sku) {

			return ['sku' => $sku];

		}, $skus);



		$this->db->simple_query("CREATE TEMPORARY TABLE sku_table (sku varchar(255))");

		$this->db->insert_batch('sku_table', $skus);



		$this->db->select('sku_table.sku')

			->from('sku_table')

			->join('products', 'products.sku = sku_table.sku', 'left')

			->group_start()

			->where('products.remove_flag', '0')

			->or_where('products.remove_flag', null)

			->group_end()

			->where('products.id', null);

		$result = $this->db->get()->result();

		return array_map(function ($row) {

			return $row->sku;

		}, $result);

	}



	public function get_product_ids_from_skus(array $skus)

	{

		$result = $this->db->query('SELECT sku, id FROM products WHERE remove_flag = 0 AND sku IN ?', [$skus])->result_array();



		return array_combine(array_column($result, 'sku'), array_column($result, 'id'));

	}



	public function check_SP__by_ID_Cust_type($product_id, $customer_type_id)

	{

		$this->db->select('*');

		$this->db->where('product_id', $product_id);

		$this->db->where('customer_type_id', $customer_type_id);

		$query = $this->db->get('products_special_prices');

		$resultArr = $query->row_array();

		//echo $this->db->last_query();

		return $resultArr;

	}



	public function getSingleproducts_special_price($special_price_id)

	{

		$this->db->select('psp.*,p.sku,p.name,p.webshop_price');

		$this->db->from('products_special_prices as psp');

		$this->db->join('products as p', 'p.id=psp.product_id');

		$this->db->where(array('psp.id' => $special_price_id));

		//$this->db->join('customers_type_master as ctm','ctm.id = psp.customer_type_id');



		$query = $this->db->get();

		$result = $query->result();

		return $result;

	}



	public function get_all_newsletter_subscriber()

	{

		$this->db->select('ns.*');

		$this->db->from('newsletter_subscriber as ns');

		$this->db->where(['ns.status' => 1]);

		$query = $this->db->get();

		$result = $query->result();

		return $result;

	}



	public function countCustomMenu($id, $code)

	{

		$this->db->select('*');

		$this->db->from('multi_lang_webshop_custom_menus');

		$this->db->where('menu_id', $id);

		$this->db->where('lang_code', $code);

		$query = $this->db->get();

		$resultArr = $query->num_rows();

		return $resultArr;

	}



	public function getMultiLangMenu($menu_id, $code)

	{

		$this->db->select('*');

		$this->db->from('multi_lang_webshop_custom_menus');

		$this->db->where('menu_id', $menu_id);

		$this->db->where('lang_code', $code);

		$query = $this->db->get();

		$resultArr = $query->row();

		return $resultArr;

	}



	public function updateMenuData($tableName, $condition, $updateData)

	{

		$this->db->where($condition);

		$this->db->update($tableName, $updateData);

		if ($this->db->affected_rows() > 0) {

			return true;

		} else {

			return false;

		}

	}



	public function get_menu_detail($id)

	{

		$this->db->select('*');

		$this->db->from('webshop_custom_menus');

		$this->db->where('id', $id);

		$query = $this->db->get();

		$resultArr = $query->row();

		return $resultArr;

	}



	public function countCmsPage($id, $code)

	{

		$this->db->select('*');

		$this->db->from('multi_lang_cms_pages');

		$this->db->where('page_id', $id);

		$this->db->where('lang_code', $code);

		$query = $this->db->get();

		$resultArr = $query->num_rows();

		return $resultArr;

	}



	public function getMultiLangPage($page_id, $code)

	{

		$this->db->select('*');

		$this->db->from('multi_lang_cms_pages');

		$this->db->where('page_id', $page_id);

		$this->db->where('lang_code', $code);

		$query = $this->db->get();

		$resultArr = $query->row();

		return $resultArr;

	}



	public function countFooterBlock($id, $code)

	{

		$this->db->select('*');

		$this->db->from('multi_lang_static_blocks');

		$this->db->where('block_id', $id);

		$this->db->where('lang_code', $code);

		$query = $this->db->get();

		$resultArr = $query->num_rows();

		return $resultArr;

	}





	public function updateStaticData($tableName, $condition, $updateData)

	{

		$this->db->where($condition);



		$this->db->update($tableName, $updateData);

		if ($this->db->affected_rows() > 0) {

			return true;

		} else {

			return false;

		}

	}



	public function getFooterBlock($block_id, $code)

	{

		$this->db->select('*');

		$this->db->from('multi_lang_static_blocks');

		$this->db->where('block_id', $block_id);

		$this->db->where('lang_code', $code);

		$query = $this->db->get();

		$resultArr = $query->row();

		return $resultArr;

	}



	public function getBannersDetails($id)

	{

		$this->db->select('*');

		$this->db->from('banners');

		$this->db->where('id', $id);

		$result = $this->db->get();

		/*$result =$this->db->query("

    		SELECT * FROM `banners` where static_block_id=;");*/

		return $result->row();

	}



	public function countHomeBlock($id, $code)

	{

		$this->db->select('*');

		$this->db->from('multi_lang_banners');

		$this->db->where('banner_id', $id);

		$this->db->where('lang_code', $code);

		$query = $this->db->get();

		$resultArr = $query->num_rows();

		return $resultArr;

	}



	public function getHomeBlock($banner_id, $code)

	{

		$this->db->select('*');

		$this->db->from('multi_lang_banners');

		$this->db->where('banner_id', $banner_id);

		$this->db->where('lang_code', $code);

		$query = $this->db->get();

		$resultArr = $query->row();

		return $resultArr;

	}



	public function getSingleS3Bucket($shop_id): string

	{

		static $results = [];



		if (isset($results[$shop_id])) {

			return $results[$shop_id];

		}



		$this->db->select('s3bucket_name');

		$this->db->from('s3bucket_setup');

		$this->db->where('shop_id', $shop_id);

		$query = $this->db->get();

		$results[$shop_id] = $query->row()->s3bucket_name;



		return $results[$shop_id];

	}



	public function countContactUs($id, $code)

	{

		$this->db->select('*');

		$this->db->from('multi_lang_website_texts');

		$this->db->where('text_id', $id);

		$this->db->where('lang_code', $code);

		$query = $this->db->get();

		$resultArr = $query->num_rows();

		return $resultArr;

	}



	public function getContactUsDetails($id)

	{

		$this->db->select('*');

		$this->db->from('website_texts');

		$this->db->where('id', $id);

		$result = $this->db->get();

		/*$result =$this->db->query("

    		SELECT * FROM `banners` where static_block_id=;");*/

		return $result->row();

	}



	public function getContactUsTrans($id, $code)

	{

		$this->db->select('*');

		$this->db->from('multi_lang_website_texts');

		$this->db->where('text_id', $id);

		$this->db->where('lang_code', $code);

		$query = $this->db->get();

		$resultArr = $query->row();

		return $resultArr;

	}



	public function getPromoTextBannersData()

	{



		$result = $this->db->get_where('promo_text_banners')->result();

		return $result;

	}



	public function countPomoTextBanners($id, $code)

	{

		$this->db->select('*');

		$this->db->from('multi_lang_promo_text_banners');

		$this->db->where('banner_id', $id);

		$this->db->where('lang_code', $code);

		$query = $this->db->get();

		$resultArr = $query->num_rows();

		return $resultArr;

	}



	public function getPromoTextDetails($id)

	{

		$this->db->select('*');

		$this->db->from('promo_text_banners');

		$this->db->where('id', $id);

		$result = $this->db->get();

		/*$result =$this->db->query("

    		SELECT * FROM `banners` where static_block_id=;");*/

		return $result->row();

	}



	public function getMultiPromoTextBanners($banner_id, $code)

	{

		$this->db->select('*');

		$this->db->from('multi_lang_promo_text_banners');

		$this->db->where('banner_id', $banner_id);

		$this->db->where('lang_code', $code);

		$query = $this->db->get();

		$resultArr = $query->row();

		return $resultArr;

	}





	public function get_countries($country_code)

	{

		$this->db->where('country_code', $country_code);

		$query = $this->db->get_where('country_master');

		$resultArr = $query->result_array();



		return $resultArr;

	}



	// End



	public function start_transaction()

	{

		$this->db->trans_start();

	}



	public function complete_transaction()

	{

		$this->db->trans_complete();

	}



	public function update_or_insert_special_pricing($row)

	{

		$now = time();

		$this->db->query("

			SET @product_id = {$row['product_id']},

				@special_price = {$row['special_price']},

				@special_price_from = {$row['special_price_from']},

				@special_price_to = {$row['special_price_to']},

				@display_original = {$row['display_original']},

				@now = {$now},

				@ip = '{$_SERVER['REMOTE_ADDR']}'");

		$this->db->query("

			INSERT INTO products_special_prices

				(product_id, special_price, special_price_from, special_price_to, display_original, created_at, ip)

			VALUES

				(@product_id, @special_price, @special_price_from, @special_price_to, @display_original, @now, @ip)

			ON DUPLICATE KEY UPDATE

				special_price = @special_price,

				special_price_from = @special_price_from,

				special_price_to = @special_price_to,

				display_original = @display_original,

				updated_at = @now,

				ip = @ip

		");

	}



	public function getVariantsByID($product_id)

	{

		$main_db_name = $this->db->database;



		$this->db->select('ev.attr_name, evp.attr_options_name, product_id');

		$this->db->from('products_variants pv');

		$this->db->join($main_db_name . '.eav_attributes ev', 'ev.id=pv.attr_id', 'INNER');

		$this->db->join($main_db_name . '.eav_attributes_options evp', 'evp.id=pv.attr_value AND ev.id = evp.attr_id', 'INNER');

		$this->db->where_in('pv.product_id', $product_id);



		$query = $this->db->get();

		$result = $query->result_array();



		return $result;

	}



	public function get_customer_type_details($Customer_type_ids)

	{

		$this->db->select('ct.name,ct.id');

		$this->db->from('customers_type_master ct');

		$this->db->where_in('id', $Customer_type_ids);

		$query = $this->db->get();

		$result = $query->result_array();

		return $result;

	}



	public function CountGetCouponCodeDiscountList($type)

	{

		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';

		$term = $_REQUEST['search']['value'];

		$this->_get_datatables_discount_coupon_code($term, $type);

		$this->db->limit($_REQUEST['length'], $_REQUEST['start']);

		$query = $this->db->get();

		return $query->num_rows();

	}



	public function get_datatables_discount_coupon_code($type)

	{

		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';

		$this->_get_datatables_discount_coupon_code($term, $type);

		$this->db->limit($_REQUEST['length'], $_REQUEST['start']);

		$query = $this->db->get();

		return $query->result();

	}

	public function FiltteredDiscountCouponCode($type)

	{

		$term = $_REQUEST['search']['value'];

		$this->_get_datatables_discount_coupon_code($term, $type);

		$query = $this->db->get();

		return $query->num_rows();

	}



	public function _get_datatables_discount_coupon_code($term, $type)

	{

		$column = array('src.coupon_code', 'sr.name', 'sr.coupon_type', 'sr.start_date', 'sr.end_date', 'sr.status', '');

		$this->db->select('sr.*, src.coupon_id, src.coupon_code');

		$this->db->from('salesrule as sr');

		$this->db->join('salesrule_coupon as src', 'src.rule_id = sr.rule_id', 'INNER');

		$this->db->where(array('sr.type' => $type, 'sr.remove_flag' => 0));

		if ($type == 4) {

			$this->db->group_by('sr.rule_id');

		}

		if ($term != '') {

			$this->db->where(" (

			    src.coupon_code LIKE '%$term%'

				OR sr.name LIKE '%$term%'

				OR sr.coupon_type LIKE '%$term%'

				OR sr.start_date LIKE '%$term%'

				OR sr.end_date LIKE '%$term%'

				 )");

		}



		if (isset($_REQUEST['order'])) // here order processing

		{

			$this->db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);

		} else {

			$this->db->order_by('sr.start_date', 'DESC');

		}

	}



	public function getAllWebshopCustomMenu($blockID)

	{

		$this->db->select('id');

		$this->db->from('webshop_custom_menus');

		$this->db->where_in('static_block_id', $blockID);

		$query = $this->db->get();

		$result = $query->result_array();

		return $result;

	}

}

