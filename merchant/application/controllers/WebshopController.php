<?php



use League\Csv\Reader;



defined('BASEPATH') or exit('No direct script access allowed');



/**

 * @property CI_Session $session

 * @property WebshopModel $WebshopModel

 * @property $db

 * @property CommonModel $CommonModel

 * @property CI_Input $input

 * @property CI_Encryption $encryption

 * @property CI_Image_lib $image_lib

 * @property Image_upload $image_upload

 */

class WebshopController extends CI_Controller

{



	function __construct()

	{

		parent::__construct();

		// $this->load->library('upload');

		// $this->load->library('encryption');

		$this->load->model('WebshopModel');

		// $this->load->model('CommonModel');

		$this->load->model('SellerProductModel');

		$this->load->model('CustomerModel');

		// $this->load->model('Multi_Languages_Model');

		// $this->load->library('Image_upload');

		// $this->load->library('S3_filesystem');



		if ($this->session->userdata('LoginID') == '') {

			redirect(base_url());

		}

	}



	public function webshopPromoTextBanners()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */



		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration/read', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$data['side_menu'] = 'webshopPromoTextBanners';

		$data['promoTextList'] = $this->WebshopModel->getPromoTextBannersData();

		$data['PageTitle'] = 'Promo Text Banners';

		$this->load->view('webshop/promo-text-banners', $data);

	}



	public function addPromoTextBanners()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration',$this->session->userdata('userPermission'))){ redirect(base_url('dashboard'));  } */



		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$promoId = $this->uri->segment(3);

		$data['side_menu'] = 'webshopPromoTextBanners';

		$data['promoText'] = $promoText = $this->WebshopModel->getSingleDataByID('promo_text_banners', array('id' => $promoId), '*');

		$data['country_master'] =  $this->CommonModel->get_shop_country_master();

		$data['PageTitle'] = 'Add Promo Text Banners';

		$this->load->view('webshop/add-new-promo-text-banners', $data);

	}



	public function savePromoText()

	{

		if (empty($_POST['background_color']) || empty($_POST['text_color'])) {

			echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

			exit;

		} else {



			$promoID = isset($_POST['promoId']) ? $_POST['promoId'] : '';

			$banner_text = $_POST['banner_text'];

			$background_color = $_POST['background_color'];

			$text_color = $_POST['text_color'];

			$status = $_POST['status'];



			if ($promoID > 0) {

				$where_arr = array('id' => $promoID);

				$updatetdata = array(

					'banner_text' => $banner_text,

					'background_color' => $background_color,

					'text_color' => $text_color,

					'status' => $status,

					'updated_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR']

				);

				$redirect = base_url('webshop/promo-text-banners');

				$rowAffected = $this->WebshopModel->updateMenuData('promo_text_banners', $where_arr, $updatetdata);

				echo json_encode(array('flag' => 1, 'msg' => "Promo Text Banners updated Successfully.", 'redirect' => $redirect));

				exit();

			} else {

				$insertdata = array(

					'banner_text' => $banner_text,

					'background_color' => $background_color,

					'text_color' => $text_color,

					'status' => $status,

					'created_by' => $_SESSION['LoginID'],

					'created_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR']

				);

				$redirect = base_url('webshop/promo-text-banners');

				$this->WebshopModel->insertData('promo_text_banners', $insertdata);

				echo json_encode(array('flag' => 1, 'msg' => "Promo Text Banners Added Successfully.", 'redirect' => $redirect));

				exit();

			}

		}

	}

	public function openePromoBannersTrans()

	{



		if (isset($_POST)) {

			$data['id'] = $id = $_POST['id'];

			$data['code'] = $code = $_POST['code'];

			$data['BannersDetails'] = $this->WebshopModel->getPromoTextDetails($id);

			$data['codeName'] = $codeName = $this->Multi_Languages_Model->getCodeName($code);

			$data['getPromoText'] =  $this->WebshopModel->getMultiPromoTextBanners($id, $code);

			$View = $this->load->view('webshop/promo-banners-text-trans', $data, true);

			$this->output->set_output($View);

		} else {

			echo "error";

			exit;

		}

	}



	public function savePromeBannersTrans()

	{

		$fbc_user_id = $_SESSION['LoginID'];

		if (isset($_POST['banner_text']) && $_POST['banner_text'] != '') {

			$id = $_POST['promoId'];

			$code = $_POST['code'];

			$checkBannersTextCount = $this->WebshopModel->countPomoTextBanners($id, $code);



			if ($checkBannersTextCount > 0) {

				$where_arr = array('banner_id' => $id, 'lang_code' => $code);

				$updatetdata = array(

					'banner_text' => $_POST['banner_text'],

					'banner_id' => $id,

					'lang_code' => $_POST['code'],

					'updated_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR'],

				);

				$vat_id = $this->WebshopModel->updateMenuData('multi_lang_promo_text_banners', $where_arr, $updatetdata);

				echo json_encode(array('flag' => 1, 'msg' => "Menu Translation Updated Successfully."));

				exit();

			} else {

				$insertdata = array(

					'banner_text' => $_POST['banner_text'],

					'banner_id' => $id,

					'lang_code' => $_POST['code'],

					'created_at' => time(),

					'created_by' => $fbc_user_id,

					'ip' => $_SERVER['REMOTE_ADDR'],

				);

				$this->WebshopModel->insertData('multi_lang_promo_text_banners', $insertdata);

				echo json_encode(array('flag' => 1, 'msg' => "Menu Translation Successfully."));

				exit();

			}

		}

	}





	public function deletePromoTextBanner()

	{

		if (isset($_POST['promoID'])) {

			$promoID = $_POST['promoID'];

			$where_promo = array('id' => $promoID);

			$where_promo_mul = array('banner_id' => $promoID);

			$rowAffected_mul = $this->WebshopModel->deleteData('multi_lang_promo_text_banners', $where_promo_mul);

			$rowAffected1 = $this->WebshopModel->deleteData('promo_text_banners', $where_promo);

			echo json_encode(array('flag' => 1, 'msg' => "Promo Text Banners deleted Successfully."));

			exit();

		}

	}



	public function webShop()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/webshop', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}

		$data['PageTitle'] = 'Webshp';

		$data['side_menu'] = 'webShop';

		$data['shopData'] = $this->WebshopModel->getSingleDataByID('shop_details', array('id' => 1), '*');



		$this->load->view('webshop/webshop_home', $data);

	}



	public function openeditmenupopup()

	{

		if (isset($_POST['id']) && $_POST['id'] != '') {

			$data['id'] = $menu_id = $_POST['id'];

			$data['code'] = $code = $_POST['code'];

			$data['menu_details'] = $this->WebshopModel->get_menu_detail($menu_id);

			$data['codeName'] = $codeName = $this->Multi_Languages_Model->getCodeName($code);

			$data['getMenu'] =  $this->WebshopModel->getMultiLangMenu($menu_id, $code);

			$View = $this->load->view('webshop/menu_translate', $data, true);

			$this->output->set_output($View);

		} else {

			echo "error";

			exit;

		}

	}



	public function saveMenuTranslate()

	{

		$fbc_user_id = $_SESSION['LoginID'];

		if (isset($_POST['menu_name']) && $_POST['menu_name'] != '') {

			$id = $_POST['hidden_menu_id'];

			$code = $_POST['code'];

			$checkMenu = $this->WebshopModel->countCustomMenu($id, $code);

			if ($checkMenu > 0) {

				$where_arr = array('menu_id' => $id, 'lang_code' => $code);

				$updatetdata = array(

					'menu_name' => $_POST['menu_name'],

					'menu_id' => $id,

					'lang_code' => $_POST['code'],

					'updated_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR'],

				);

				$vat_id = $this->WebshopModel->updateMenuData('multi_lang_webshop_custom_menus', $where_arr, $updatetdata);

				echo json_encode(array('flag' => 1, 'msg' => "Menu Translation Updated Successfully."));

				exit();

			} else {

				$insertdata = array(

					'menu_name' => $_POST['menu_name'],

					'menu_id' => $id,

					'lang_code' => $_POST['code'],

					'created_at' => time(),

					'created_by' => $fbc_user_id,

					'ip' => $_SERVER['REMOTE_ADDR'],

				);

				$this->WebshopModel->insertData('multi_lang_webshop_custom_menus', $insertdata);

				echo json_encode(array('flag' => 1, 'msg' => "Menu Translation Successfully."));

				exit();

			}

		}

	}



	public function submitShopAccess()

	{

		if (empty($_POST) || empty($_POST)) {

			echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

			exit;

		} else {





			$websiteLive = 0;

			if (isset($_POST['websiteLive'])) {

				$websiteLive = 1;

			}



			$enable_test_mode = 0;

			if (isset($_POST['test_mode'])) {

				$enable_test_mode = 1;

			}



			$website_domain_name = 0;

			if (isset($_POST['domainName'])) {

				$website_domain_name = $_POST['domainName'];

			}

			$website_default_url = 0;

			if (isset($_POST['default_url'])) {

				$website_default_url = $_POST['default_url'];

			}



			$updateData = array(

				'webshop_status'	=> 1,

				'website_live'	=> $websiteLive,

				'enable_test_mode' => $enable_test_mode,

				'website_domain_name' => $website_domain_name,

				'website_default_url' => $website_default_url,

				'test_mode_access_ips' => $_POST['ip_addresses'],

			);



			$this->db->where(array('id' => 1));

			$rowAffected = $this->db->update('shop_details', $updateData);

			if ($rowAffected) {

				echo json_encode(array('flag' => 1, 'msg' => "Success",));

				exit();

			} else {

				echo json_encode(array('flag' => 0, 'msg' => "went something wrong!"));

				exit;

			}

		}

	}





	public function webshopThemes()

	{

		$data['side_menu'] = 'webshopThemes';

		$data['themes'] = $this->WebshopModel->getThemesData();

		$data['themeID'] = $this->WebshopModel->getActiveTheme();

		$data['PageTitle'] = 'Themes';

		$this->load->view('webshop/webshop_themes', $data);

	}



	public function changeTheme()

	{

		//$shop_id = $this->session->userdata('ShopID');



		$id = $_POST['themeID'];

		$theme = $this->WebshopModel->getThemeData($id);

		if ($theme) {

			$update = array('current_theme' => 0);

			$where_arr = 1;

			$this->WebshopModel->updateData('themes_webshops', $update);

			$insertdata = array(

				'theme_id' => $theme->id,

				//'theme_code'=>$theme->theme_code,

				//'theme_name'=>$theme->theme_name,

				'created_by' => $_SESSION['LoginID'],

				'current_theme' => 1,

				'created_at' => time(),

				'ip' => $_SERVER['REMOTE_ADDR']

			);

			$theme_id = $this->WebshopModel->insertData('themes_webshops', $insertdata);

			if ($theme_id) {

				$insertlogsdata = array(

					'theme_id' => $theme->id,

					'webshop_theme_id' => $theme_id,

					'created_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR']

				);

				$theme_log_id = $this->WebshopModel->insertDBdata('themes_logs', $insertlogsdata);



				echo json_encode(array('flag' => 1, 'msg' => "Success",));

				exit();

			} else {

				echo json_encode(array('flag' => 0, 'msg' => "went something wrong!"));

				exit;

			}

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "nothing to change please try again"));

			exit;

		}

	}



	public function webshopCustomizePages()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration/read', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$data['side_menu'] = 'webshopThemes';

		$data['pages'] = $this->WebshopModel->getPages();

		// $data['shopDetail'] = $this->WebshopModel->getShopData($_SESSION['ShopOwnerId'],$_SESSION['ShopID']);

		$data['PageTitle'] = 'Customize Pages';

		$this->load->view('webshop/webshop_customize_pages', $data);

	}



	public function CustomizePagesAdd()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$data['side_menu'] = 'webshopThemes';

		$data['PageTitle'] = 'Customize Pages Add';

		$this->load->view('webshop/webshop_pages_add', $data);

	}



	public function CustomizePagesEdit()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$data['side_menu'] = 'webshopThemes';

		$pageId = $this->uri->segment(4);

		$data['page'] = $this->WebshopModel->getPageDataId($pageId);

		$data['PageTitle'] = 'Customize Pages Edit';

		// $data['languagesListing'] = $this->Multi_Languages_Model->getLanguages();

		$this->load->view('webshop/webshop_pages_add', $data);

	}



	public function openeditcmspopup()

	{

		if (isset($_POST['cms_id']) && $_POST['cms_id'] != '') {

			$cms_id = $_POST['cms_id'];

			$data['cms_id'] = $cms_id;

			$data['code'] = $code = $_POST['code'];

			$data['codeName'] = $codeName = $this->Multi_Languages_Model->getCodeName($code);

			$data['getPage'] =  $this->WebshopModel->getMultiLangPage($cms_id, $code);

			$data['CmsPage'] = $this->WebshopModel->getPageDataId($cms_id);

			$View = $this->load->view('webshop/wepshop_lang_cms', $data, true);

			$this->output->set_output($View);

		} else {

			echo "error";

			exit;

		}

	}



	function saveCmsTranslate()

	{

		$fbc_user_id = $_SESSION['LoginID'];

		if (isset($_POST['pageTitle_lang']) && $_POST['pageTitle_lang'] != '') {

			$id = $_POST['hidden_cms_id'];

			$code = $_POST['code'];

			$checkPage = $this->WebshopModel->countCmsPage($id, $code);

			if ($checkPage > 0) {

				$where_arr = array('page_id' => $id, 'lang_code' => $code);

				$updatetdata = array(

					'page_id' => $id,

					'lang_code' => $_POST['code'],

					'title' => $_POST['pageTitle_lang'],

					'content' => $_POST['pageContent_lang'],

					'meta_title' => $_POST['metaTitle_lang'],

					'meta_description' => $_POST['metaDescription_lang'],

					'meta_keywords	' => $_POST['metaKeyword_lang'],

					'updated_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR'],

				);

				$vat_id = $this->WebshopModel->updateMenuData('multi_lang_cms_pages', $where_arr, $updatetdata);

				echo json_encode(array('flag' => 1, 'msg' => "Page Translation Updated Successfully."));

				exit();

			} else {

				$insertdata = array(

					'page_id' => $id,

					'lang_code' => $_POST['code'],

					'title' => $_POST['pageTitle_lang'],

					'content' => $_POST['pageContent_lang'],

					'meta_title' => $_POST['metaTitle_lang'],

					'meta_description' => $_POST['metaDescription_lang'],

					'meta_keywords	' => $_POST['metaKeyword_lang'],

					'created_by' => $fbc_user_id,

					'created_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR'],

				);

				$this->WebshopModel->insertData('multi_lang_cms_pages', $insertdata);

				echo json_encode(array('flag' => 1, 'msg' => "Page Translation Successfully."));

				exit();

			}

		}

	}



	public function submitCMSPage()

	{

		if (empty($_POST['pageTitle']) || empty($_POST['pageIdentifier']) || empty($_POST['pageContent'])) {

			echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

			exit;

		} else {

			$identifier = $this->CommonModel->custom_filter_input($_POST['pageIdentifier']);

			$nIdentifier = $identifier;

			$nIdentifier = str_replace(" ", "-", trim($nIdentifier));

			$nIdentifier = preg_replace('/[^A-Za-z0-9-]/', '', $nIdentifier);

			$nIdentifier = strtolower($nIdentifier);



			$page_id = $_POST['page_id'];

			if ($page_id) {

				$newPage_id = $page_id;

			} else {

				$newPage_id = 0;

			}

			$foundIdentifier = $this->WebshopModel->getIdentifier($nIdentifier, $newPage_id);

			if ($foundIdentifier) {

				echo json_encode(array('flag' => 0, 'msg' => "Identifier Exist!"));

				exit;

			}



			$pageTitle = $this->CommonModel->custom_filter_input($_POST['pageTitle']);

			$metaTitle = isset($_POST['metaTitle']) ? $this->CommonModel->custom_filter_input($_POST['metaTitle']) : '';

			$metaKeyword = isset($_POST['metaKeyword']) ? $this->CommonModel->custom_filter_input($_POST['metaKeyword']) : '';

			$metaDescription = isset($_POST['metaDescription']) ? $this->CommonModel->custom_filter_input($_POST['metaDescription']) : '';



			if ($page_id) {

				//Update

				$where_arr = array('id' => $page_id);

				$update = array(

					'title' => $pageTitle,

					'identifier' => $nIdentifier,

					'content' => $_POST['pageContent'],

					'meta_title' => $metaTitle,

					'meta_keywords' => $metaKeyword,

					'meta_description' => $metaDescription,

					'status' => $_POST['status'],

					'updated_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR']

				);



				$rowAffected = $this->WebshopModel->updateNewData('cms_pages', $where_arr, $update);

				if ($rowAffected) {

					$redirect = base_url('webshop/customize-pages');

					echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

					exit();

				} else {

					echo json_encode(array('flag' => 0, 'msg' => "nothing to update!"));

					exit;

				}

			} else {

				//Insert

				$insertdata = array(

					'title' => $pageTitle,

					'identifier' => $nIdentifier,

					'content' => $_POST['pageContent'],

					'meta_title' => $metaTitle,

					'meta_keywords' => $metaKeyword,

					'meta_description' => $metaDescription,

					'status' => $_POST['status'],

					'created_by' => $_SESSION['LoginID'],

					'created_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR']

				);

				$page_id = $this->WebshopModel->insertData('cms_pages', $insertdata);

				if ($page_id) {

					$redirect = base_url('webshop/customize-pages');

					echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

					exit();

				} else {

					echo json_encode(array('flag' => 0, 'msg' => "went something wrong!"));

					exit;

				}

			}

		}

	}



	public function deleteCMAPage()

	{

		$page_id = $_POST['cmsPageID'];

		if ($page_id) {

			$where_arr = array('id' => $page_id);

			$update = array(

				'remove_flag' => 1,

				'updated_at' => time(),

				'ip' => $_SERVER['REMOTE_ADDR']

			);

			$rowAffected = $this->WebshopModel->updateNewData('cms_pages', $where_arr, $update);

			if ($rowAffected) {

				$redirect = base_url('webshop/customize-pages');

				echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

				exit();

			} else {

				echo json_encode(array('flag' => 0, 'msg' => "nothing to update!"));

				exit;

			}

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "nothing to update!"));

			exit;

		}

	}



	public function addCustomMenu()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$data['side_menu'] = 'webshopStaticBlocks';

		$blockID = $this->uri->segment(3);

		$data['blockID'] = $blockID;

		$data['cmsPages'] = $this->WebshopModel->getPages();

		$data['browse_category'] = $this->WebshopModel->getAllCategories();

		$data['browse_menus'] = $this->WebshopModel->getAllMenus($blockID);

		$data['PageTitle'] = 'Add Custom Menu';

		$this->load->view('webshop/add_custom_menu', $data);

	}



	public function editCustomMenu()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */



		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$menuID = $this->uri->segment(3);

		$data['sMenus'] = $sMenus = $this->WebshopModel->getSingleDataByID('webshop_custom_menus', array('id' => $menuID), '*');

		$data['blockID'] = $sMenus->static_block_id;

		$data['cmsPages'] = $this->WebshopModel->getPages();

		$data['browse_category'] = $this->WebshopModel->getAllCategories();

		$data['browse_menus'] = $this->WebshopModel->getAllMenus($sMenus->static_block_id, $menuID);

		$data['side_menu'] = 'webshopStaticBlocks';

		$data['PageTitle'] = 'Edit Custom Menus';

		$this->load->view('webshop/add_custom_menu', $data);

	}



	public function saveCustomMenu()

	{

		if (empty($_POST['menu_name']) || empty($_POST['m_type'])) {

			echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

			exit;

		} else {

			$custom_link = (isset($_POST['m_type']) && $_POST['m_type'] == 1) ? $_POST['m_cust_link'] : '';

			$page_id = (isset($_POST['m_type']) && $_POST['m_type'] == 2) ? $_POST['m_page'] : 0;

			$category_id = (isset($_POST['m_type']) && $_POST['m_type'] == 3) ? $_POST['m_category'] : 0;

			$blockID = $_POST['block_id'];



			$menu_arr = explode(",", $_POST['m_menu']);



			if (isset($_POST['m_menu']) && $_POST['m_menu'] == 'none') {

				$menu_parent_id = 0;

				$menu_main_parent_id = 0;

				$menu_level = 0;

			} else if ($_POST['m_menu'] != 'none' && count($menu_arr) == 1) {

				$menu_parent_id = $_POST['m_menu'];

				$menu_main_parent_id = $_POST['m_menu'];

				$menu_level = 1;

			} else if ($_POST['m_menu'] != 'none' && count($menu_arr) == 2) {

				$menu_parent_id = $menu_arr[1];

				$menu_main_parent_id = $menu_arr[0];

				$menu_level = 2;

			} else if ($_POST['m_menu'] != 'none' && count($menu_arr) == 3) {

				$menu_parent_id = $menu_arr[2];

				$menu_main_parent_id = $menu_arr[0];

				$menu_level = 3;

			}



			$menu_id = isset($_POST['menu_id']) ? $_POST['menu_id'] : '';

			if ($menu_id) {

				//Update

				$where_arr = array('id' => $menu_id);

				$update = array(

					'menu_parent_id' => $menu_parent_id,

					'menu_main_parent_id' => $menu_main_parent_id,

					'menu_level' => $menu_level,

					'menu_name' => $_POST['menu_name'],

					'menu_type' => $_POST['m_type'],

					'menu_custom_url' => $custom_link,

					'page_id' => $page_id,

					'category_id' => $category_id,

					'position' => $_POST['position'],

					'status' => $_POST['cust_menu_status'],

					'updated_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR']

				);



				$rowAffected = $this->WebshopModel->updateNewData('webshop_custom_menus', $where_arr, $update);

				if ($rowAffected) {

					$redirect = base_url('webshop/static-blocks/menu/' . $blockID);

					echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

					exit();

				} else {

					echo json_encode(array('flag' => 0, 'msg' => "nothing to update!"));

					exit;

				}

			} else {

				$insertdata = array(

					'static_block_id' => $_POST['block_id'],

					'menu_parent_id' => $menu_parent_id,

					'menu_main_parent_id' => $menu_main_parent_id,

					'menu_level' => $menu_level,

					'menu_name' => $_POST['menu_name'],

					'menu_type' => $_POST['m_type'],

					'menu_custom_url' => $custom_link,

					'page_id' => $page_id,

					'category_id' => $category_id,

					'position' => $_POST['position'],

					'status' => $_POST['cust_menu_status'],

					'created_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR']

				);



				$custom_menus = $this->WebshopModel->insertData('webshop_custom_menus', $insertdata);

				if ($custom_menus) {

					$redirect = base_url('webshop/static-blocks/menu/' . $blockID);

					echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

					exit();

				} else {

					echo json_encode(array('flag' => 0, 'msg' => "went something wrong!"));

					exit;

				}

			}

		}

	}



	public function webshopStaticBlocks()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */



		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration/read', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}

		$data['side_menu'] = 'webshopStaticBlocks';

		$data['staticBlocks'] = $this->WebshopModel->getStaticBlocks();

		$data['PageTitle'] = 'Static Blocks';

		$this->load->view('webshop/webshop_static_blocks', $data);

	}



	public function addStaticBlocks()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}

		$data['side_menu'] = 'webshopStaticBlocks';

		$data['PageTitle'] = 'Add Static Blocks';

		$this->load->view('webshop/add_static_blocks', $data);

	}



	public function openeditfooter()

	{

		if (isset($_POST['id']) && $_POST['id'] != '') {

			$id = $_POST['id'];

			$data['id'] = $id;

			$data['code'] = $code = $_POST['code'];

			$data['codeName'] = $codeName = $this->Multi_Languages_Model->getCodeName($code);

			$data['getFooterBlock'] =  $this->WebshopModel->getFooterBlock($id, $code);

			$data['sBlock'] = $this->WebshopModel->getSingleDataByID('static_blocks', array('id' => $id), '*');

			$View = $this->load->view('webshop/static_blocks_translations', $data, true);

			$this->output->set_output($View);

		} else {

			echo "error";

			exit;

		}

	}



	function saveFooterTranslate()

	{

		$fbc_user_id = $_SESSION['LoginID'];

		if (isset($_POST['blockTitle_lang']) && $_POST['blockTitle_lang'] != '') {

			$id = $_POST['hidden_footer_id'];

			$code = $_POST['code'];

			$checkBlock = $this->WebshopModel->countFooterBlock($id, $code);

			if ($checkBlock > 0) {

				$where_arr = array('block_id' => $id, 'lang_code' => $code);

				$updatetdata = array(

					'block_id' => $id,

					'lang_code' => $_POST['code'],

					'title' => $_POST['blockTitle_lang'],

					'content' => $_POST['blockContent_lang'],

					'updated_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR'],

				);

				$vat_id = $this->WebshopModel->updateMenuData('multi_lang_static_blocks', $where_arr, $updatetdata);

				echo json_encode(array('flag' => 1, 'msg' => "Block Translation Updated Successfully."));

				exit();

			} else {

				$insertdata = array(

					'block_id' => $id,

					'lang_code' => $_POST['code'],

					'title' => $_POST['blockTitle_lang'],

					'content' => $_POST['blockContent_lang'],

					'created_by' => $fbc_user_id,

					'created_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR'],

				);

				$this->WebshopModel->insertData('multi_lang_static_blocks', $insertdata);

				echo json_encode(array('flag' => 1, 'msg' => " Block Translation Added Successfully."));

				exit();

			}

		}

	}



	public function editStaticBlocks()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$blockID = $this->uri->segment(3);

		$data['sBlock'] = $this->WebshopModel->getSingleDataByID('static_blocks', array('id' => $blockID), '*');

		$data['side_menu'] = 'webshopStaticBlocks';

		$data['PageTitle'] = 'Edit Static Blocks';

		// $data['languagesListing'] = $this->Multi_Languages_Model->getLanguages();

		$this->load->view('webshop/add_static_blocks', $data);

	}





	public function submitStaticBlock()

	{

		if (empty($_POST['blockTitle']) || empty($_POST['blockIdentifier'])) {

			echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

			exit;

		} else {

			$identifier = $this->CommonModel->custom_filter_input($_POST['blockIdentifier']);

			$nIdentifier = $identifier;

			$nIdentifier = str_replace(" ", "-", trim($nIdentifier));

			$nIdentifier = preg_replace('/[^A-Za-z0-9-]/', '', $nIdentifier);

			$nIdentifier = strtolower($nIdentifier);



			$block_id = $_POST['block_id'];

			if ($block_id) {

				$newblock_id = $block_id;

			} else {

				$newblock_id = 0;

			}

			$foundIdentifier = $this->WebshopModel->getBlockIdentifier($nIdentifier, $newblock_id);

			if ($foundIdentifier) {

				echo json_encode(array('flag' => 0, 'msg' => "Identifier Exist!"));

				exit;

			}

			$blockTitle = $this->CommonModel->custom_filter_input($_POST['blockTitle']);



			if ($block_id) {

				//Update

				$where_arr = array('id' => $block_id);

				$update = array(

					'title' => $blockTitle,

					'identifier' => $nIdentifier,

					'content' => $_POST['blockContent'],

					'status' => $_POST['status'],

					'updated_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR']

				);



				$rowAffected = $this->WebshopModel->updateNewData('static_blocks', $where_arr, $update);

				if ($rowAffected) {

					$redirect = base_url('webshop/static-blocks');

					echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

					exit();

				} else {

					echo json_encode(array('flag' => 0, 'msg' => "nothing to update!"));

					exit;

				}

			} else {

				//Insert

				if (empty($_POST['blockType'])) {

					echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

					exit;

				} else {

					$insertdata = array(

						'type' => $_POST['blockType'],

						'title' => $blockTitle,

						'identifier' => $nIdentifier,

						'content' => $_POST['blockContent'],

						'status' => $_POST['status'],

						'created_by' => $_SESSION['LoginID'],

						'created_at' => time(),

						'ip' => $_SERVER['REMOTE_ADDR']

					);

					$block_id = $this->WebshopModel->insertData('static_blocks', $insertdata);

					if ($block_id) {

						$redirect = base_url('webshop/static-blocks');

						echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

						exit();

					} else {

						echo json_encode(array('flag' => 0, 'msg' => "went something wrong!"));

						exit;

					}

				}

			}

		}

	}



	public function deleteCustomMenu()

	{

		$blockID = $_POST['blockID'];

		if ($blockID) {

			$rowAffected = $this->WebshopModel->deleteCustomMenu($blockID);

			if ($rowAffected) {

				$redirect = base_url('webshop/static-blocks');

				echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

				exit();

			} else {

				echo json_encode(array('flag' => 0, 'msg' => "nothing to Delete!"));

				exit;

			}

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "nothing to delete!"));

			exit;

		}

	}



	public function deleteBlock()

	{

		$blockID = $_POST['blockID'];

		if ($blockID) {

			$where_arr = array('id' => $blockID);

			$where_banner = array('static_block_id' => $blockID);

			$rowAffected = $this->WebshopModel->deleteData('static_blocks', $where_arr);

			if ($rowAffected) {

				$this->WebshopModel->deleteData('banners', $where_banner);

				$redirect = base_url('webshop/static-blocks');

				echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

				exit();

			} else {

				echo json_encode(array('flag' => 0, 'msg' => "nothing to Delete!"));

				exit;

			}

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "nothing to delete!"));

			exit;

		}

	}





	public function bannerStaticBlocks()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$data['side_menu'] = 'webshopStaticBlocks';

		$blockID = $this->uri->segment(4);

		$data['blockID'] = $blockID;

		$data['static_blocks_info'] = $this->WebshopModel->getMenuType($blockID);



		$data['browse_category'] = $this->WebshopModel->getAllCategories($blockID);

		//echo '<pre>';print_r($data['browse_category']);exit;



		$data['banners'] = $this->WebshopModel->getBanners($blockID);

		$data['CustomerTypeMaster'] = $this->SellerProductModel->getMultiDataById('customers_type_master', array(), '');



		//  echo '<pre>';print_r($data['banners']);exit;



		if ($data['static_blocks_info']->identifier == 'homeblock1') {

			$data['PageTitle'] = 'Add homeblock';

			$this->load->view('webshop/homeblock', $data);

		} elseif ($data['static_blocks_info']->identifier == 'footer-block-3') {

			$data['PageTitle'] = 'Add FooterBlock';

			$this->load->view('webshop/block_footer', $data);

		} else {

			$data['PageTitle'] = 'Add Banner';

			$this->load->view('webshop/block_banner', $data);

		}

	}



	public function openeHomeBlock()

	{

		if (isset($_POST['id']) && $_POST['id'] != '') {

			$data['id'] = $id = $_POST['id'];

			$data['code'] = $code = $_POST['code'];

			$data['banners'] = $this->WebshopModel->getBannersDetails($id);

			$data['codeName'] = $codeName = $this->Multi_Languages_Model->getCodeName($code);

			$data['getHomeBlock'] =  $this->WebshopModel->getHomeBlock($id, $code);

			$View = $this->load->view('webshop/homeblock_translations.php', $data, true);

			$this->output->set_output($View);

		} else {

			echo "error";

			exit;

		}

	}





	function saveHomeBlockTranslate()

	{

		$fbc_user_id = $_SESSION['LoginID'];

		if (isset($_POST) && $_POST != '') {

			$id = $_POST['hidden_home_id'];

			$code = $_POST['code'];

			$checkHomeBlock = $this->WebshopModel->countHomeBlock($id, $code);

			if ($checkHomeBlock > 0) {

				$where_arr = array('banner_id' => $id, 'lang_code' => $code);

				$updatetdata = array(

					'heading' => $_POST['heading_lang'],

					'button_text' => $_POST['description_lang'],

					'banner_id' => $id,

					'lang_code' => $_POST['code'],

					'updated_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR'],

				);

				$vat_id = $this->WebshopModel->updateMenuData('multi_lang_banners', $where_arr, $updatetdata);

				echo json_encode(array('flag' => 1, 'msg' => "Home Block Translation Updated Successfully."));

				exit();

			} else {

				$insertdata = array(

					'heading' => $_POST['heading_lang'],

					'button_text' => $_POST['description_lang'],

					'banner_id' => $id,

					'lang_code' => $_POST['code'],

					'created_at' => time(),

					'created_by' => $fbc_user_id,

					'ip' => $_SERVER['REMOTE_ADDR'],

				);

				$this->WebshopModel->insertData('multi_lang_banners', $insertdata);

				echo json_encode(array('flag' => 1, 'msg' => "Home Block Translation Successfully."));

				exit();

			}

		}

	}



	public function openeFooterBlock()

	{

		if (isset($_POST['id']) && $_POST['id'] != '') {

			$data['id'] = $id = $_POST['id'];

			$data['code'] = $code = $_POST['code'];

			$data['banners'] = $this->WebshopModel->getBannersDetails($id);

			$data['codeName'] = $codeName = $this->Multi_Languages_Model->getCodeName($code);

			$data['getHomeBlock'] =  $this->WebshopModel->getHomeBlock($id, $code);

			$View = $this->load->view('webshop/footerblock_translations.php', $data, true);

			$this->output->set_output($View);

		} else {

			echo "error";

			exit;

		}

	}



	function saveFooterBlockTranslate()

	{

		$fbc_user_id = $_SESSION['LoginID'];

		if (isset($_POST) && $_POST != '') {

			$id = $_POST['hidden_footer_id'];

			$code = $_POST['code'];

			$checkHomeBlock = $this->WebshopModel->countHomeBlock($id, $code);

			if ($checkHomeBlock > 0) {

				$where_arr = array('banner_id' => $id, 'lang_code' => $code);

				$updatetdata = array(

					'heading' => $_POST['bannerHeading_lang'],

					'banner_id' => $id,

					'lang_code' => $_POST['code'],

					'updated_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR'],

				);

				$vat_id = $this->WebshopModel->updateMenuData('multi_lang_banners', $where_arr, $updatetdata);

				echo json_encode(array('flag' => 1, 'msg' => "Footer Block Translation Updated Successfully."));

				exit();

			} else {

				$insertdata = array(

					'heading' => $_POST['bannerHeading_lang'],

					'banner_id' => $id,

					'lang_code' => $_POST['code'],

					'created_at' => time(),

					'created_by' => $fbc_user_id,

					'ip' => $_SERVER['REMOTE_ADDR'],

				);

				$this->WebshopModel->insertData('multi_lang_banners', $insertdata);

				echo json_encode(array('flag' => 1, 'msg' => "Footer Block Translation Successfully."));

				exit();

			}

		}

	}



	public function openeBanner()

	{

		if (isset($_POST['id']) && $_POST['id'] != '') {

			$data['id'] = $id = $_POST['id'];

			$data['code'] = $code = $_POST['code'];

			$data['banners'] = $this->WebshopModel->getBannersDetails($id);

			$data['codeName'] = $codeName = $this->Multi_Languages_Model->getCodeName($code);

			$data['getBanners'] =  $this->WebshopModel->getHomeBlock($id, $code);

			$View = $this->load->view('webshop/banner_trans.php', $data, true);

			$this->output->set_output($View);

		} else {

			echo "error";

			exit;

		}

	}





	function SaveBanners()

	{

		$fbc_user_id = $_SESSION['LoginID'];

		if (isset($_POST) && $_POST != '') {

			$id = $_POST['hidden_banner_id'];

			$code = $_POST['code'];

			$checkHomeBlock = $this->WebshopModel->countHomeBlock($id, $code);

			if ($checkHomeBlock > 0) {

				$where_arr = array('banner_id' => $id, 'lang_code' => $code);

				$updatetdata = array(

					'heading' => $_POST['banner_heading_lang'],

					'banner_id' => $id,

					'button_text' => $_POST['buttonText_lang'],

					'description' => $_POST['desc_lang'],



					'lang_code' => $_POST['code'],

					'updated_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR'],

				);

				$vat_id = $this->WebshopModel->updateMenuData('multi_lang_banners', $where_arr, $updatetdata);

				echo json_encode(array('flag' => 1, 'msg' => "Banner Translation Updated Successfully."));

				exit();

			} else {

				$insertdata = array(

					'heading' => $_POST['banner_heading_lang'],

					'banner_id' => $id,

					'button_text' => $_POST['buttonText_lang'],

					'description' => $_POST['desc_lang'],



					'lang_code' => $_POST['code'],

					'created_at' => time(),

					'created_by' => $fbc_user_id,

					'ip' => $_SERVER['REMOTE_ADDR'],

				);

				$this->WebshopModel->insertData('multi_lang_banners', $insertdata);

				echo json_encode(array('flag' => 1, 'msg' => "Banners Add Translation Successfully."));

				exit();

			}

		}

	}



	public function menuStaticBlocks()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}

		$data['side_menu'] = 'webshopStaticBlocks';

		$blockID = $this->uri->segment(4);

		$data['blockID'] = $blockID;

		$data['menuType'] = $this->WebshopModel->getMenuType($blockID);

		$data['categoryMenu'] = $this->WebshopModel->getCatMenus($blockID);

		$data['customMenu'] = $this->WebshopModel->getCustomMenus($blockID);

		$data['browse_category'] = $this->WebshopModel->getAllCategories($blockID);

		//$data['languagesListing'] = $this->Multi_Languages_Model->getLanguages();

		$data['PageTitle'] = 'Add Menu';

		$this->load->view('webshop/block_menu', $data);

	}



	public function saveTopMenuPosition()

	{

		if (isset($_POST['blockID'])) {

			$blockID = $_POST['blockID'];

			$customMenuData = $this->WebshopModel->getAllWebshopCustomMenu($blockID);

			if (isset($customMenuData)) {

				foreach ($customMenuData as $value) {

					$id = $value['id'];

					$position = $_POST['position_' . $id];

					$where_arr = array('id' => $id);

					$update = array('position' => $position);

					$rowAffected = $this->WebshopModel->updateNewData('webshop_custom_menus', $where_arr, $update);

				}

				echo json_encode(array('flag' => 1, 'msg' => "success"));

				exit;

			} else {

				echo json_encode(array('flag' => 2, 'msg' => "Something went wrong!"));

				exit;

			}

		} else {

			echo json_encode(array('flag' => 2, 'msg' => "Something went wrong!"));

			exit;

		}

	}





	public function changeMenu()

	{

		$shop_id = $this->session->userdata('ShopID');



		$id = $_POST['menuType'];

		$blockID = $_POST['block_ID'];



		$update = array('menu_type' => $id);

		$where_arr = array('id' => $blockID);

		$rowAffected = $this->WebshopModel->updateNewData('static_blocks', $where_arr, $update);

		if ($rowAffected) {

			echo json_encode(array('flag' => 1, 'msg' => "Success"));

			exit;

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "nothing to change please try again"));

			exit;

		}

	}



	public function saveCategoryMenu()

	{
		// echo "<pre>";print_r($_POST);die;
		// echo '<pre>';
		// print_r($_POST);
		// exit;


		$fbc_user_id = $this->session->userdata('LoginID');
		$shop_id = $this->session->userdata('ShopID');

		if (isset($_POST)) {
			$chkMenuArray = isset($_POST['chk_cat_menu']) ? $_POST['chk_cat_menu'] : array();
			$block_id = isset($_POST['block_id']) ? $this->CommonModel->custom_filter_input($_POST['block_id']) : '';
			if (empty($chkMenuArray) || $block_id == '') {
				echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory field"));
				exit;
			} else {
				$chkMenuArray = $_POST['chk_cat_menu'];
				$where_arr = array('static_block_id' => $block_id);
				$selectedRow = $this->WebshopModel->getWhere('webshop_cat_menus', $where_arr);
				if (count($selectedRow) > 0) {
					$rowAffected = $this->WebshopModel->deleteData('webshop_cat_menus', $where_arr);
					if ($rowAffected) {
						foreach ($chkMenuArray as $value) {
							$insertdata = array(
								'static_block_id' => $block_id,
								'category_id' => $value,
								'position' => isset($_POST['position_' . $value]) && $_POST['position_' . $value] !== '' 
									? $_POST['position_' . $value] 
									: 0,
								'created_at' => time(),
								'ip' => $_SERVER['REMOTE_ADDR']
							);
							$cat_menu = $this->WebshopModel->insertData('webshop_cat_menus', $insertdata);
						}
					}
				} else {
					foreach ($chkMenuArray as $value) {
						$insertdata = array(
							'static_block_id' => $block_id,
							'category_id' => $value,
							'position' => isset($_POST['position_' . $value]) && $_POST['position_' . $value] !== '' 
									? $_POST['position_' . $value] 
									: 0,
							'created_at' => time(),
							'ip' => $_SERVER['REMOTE_ADDR']
						);
						$cat_menu = $this->WebshopModel->insertData('webshop_cat_menus', $insertdata);
					}
				}
				if ($cat_menu) {
					$redirect = base_url('webshop/static-blocks');
					echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));
					exit;
				} else {
					echo json_encode(array('flag' => 0, 'msg' => "went somthing wrong!"));
					exit;
				}
			}
		} else {
			echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory field"));
			exit;
		}
	}





	public function submitBanners()

	{

		$fbc_user_id = $this->session->userdata('LoginID');

		$shop_id = $this->session->userdata('ShopID');



		$empty_banner = 0;

		foreach ($_POST['imageName'] as $key => $bannerImage) {

			$bannerImage = trim($bannerImage);

			if (empty($bannerImage)) {

				$empty_banner = 1;

			}

		}



		if ($empty_banner === 1) {

			echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory field"));

			exit;

		}



		$bannerIDCount = count($_POST['bannerCount']);



		for ($i = 0; $i < $bannerIDCount; $i++) {



			$heading = isset($_POST['bannerHeading'][$i]) ? $this->CommonModel->custom_filter_input($_POST['bannerHeading'][$i]) : '';

			$button_text = isset($_POST['buttonText'][$i]) ? $this->CommonModel->custom_filter_input($_POST['buttonText'][$i]) : '';

			$link_button_to = isset($_POST['buttonLink'][$i]) ? $this->CommonModel->custom_filter_input($_POST['buttonLink'][$i]) : '';



			if (isset($_POST['types_ids']) && $_POST['types_ids'] != '') {

				$types_ids = $_POST['types_ids'];

				$customer_type_ids = explode('-', $types_ids);

				$result = preg_replace('/^,+|,+$/', '', $customer_type_ids);

				$custumer_type_id = array_filter($result, 'strlen');

			}

			if (isset($custumer_type_id) && count($custumer_type_id) > 0) {

				$customer_type_ids_str = $custumer_type_id;

			} else {

				$customer_type_ids_str = '';

			}



			$start_date = (isset($_POST['start_date'][$i]) && $_POST['start_date'][$i] != '') ? $_POST['start_date'][$i] : '';

			$end_date = (isset($_POST['end_date'][$i]) && $_POST['end_date'][$i] != '') ? $_POST['end_date'][$i] : '';



			if ($start_date != '' || $end_date != '') {

				$start_date_Arr = $_POST['start_dateArr'];

				$explode_start_date = explode('+', $start_date_Arr);

				$start_date_result = preg_replace('/^,+|,+$/', '', $explode_start_date);

				$filter_start_date = array_filter($start_date_result, 'strlen');



				$end_date_Arr = $_POST['end_dateArr'];

				$explode_end_date = explode('+', $end_date_Arr);

				$end_date_result = preg_replace('/^,+|,+$/', '', $explode_end_date);

				$filter_end_date = array_filter($end_date_result, 'strlen');



				if ($filter_start_date[$i] == 0 && $filter_end_date[$i] != 0) {

					$BannerNo = $i + 1;

					echo json_encode(array(

						'flag' => 0,

						'msg' => "Banner " . $BannerNo . " - Please also select start date ",

					));

					exit;

				}

				if ($filter_start_date[$i] != 0 && $filter_end_date[$i] == 0) {

					$BannerNo = $i + 1;

					echo json_encode(array(

						'flag' => 0,

						'msg' => "Banner " . $BannerNo . " - Please also select end date",

					));

					exit;

				}

			}

			if ($start_date != '' && $end_date != '') {

				$start_date = date('Y-m-d', strtotime($start_date));

				$end_date = date('Y-m-d', strtotime($end_date));

			} else {

				$start_date = '0000-00-00';

				$end_date = '0000-00-00';

			}



			if (isset($_POST['m_category' . ($i + 1)]) && $_POST['m_category' . ($i + 1)] != '') {

				$values = $_POST['m_category' . ($i + 1)];

				$category_ids = ',' . implode(',', $values) . ',';

			} else {

				$category_ids = '';

			}



			$dfileName = $_POST['imageName'][$i];



			$data = array(

				'static_block_id' => $_POST['block_id'],

				'position' => $_POST['position'][$i],

				'heading' => $heading,

				'description' => $_POST['bannerDescription'][$i],

				'type' => $_POST['bannerType'][$i],

				'status' => $_POST['status'][$i],

				'start_date' => $start_date,

				'end_date' => $end_date,

				'customer_type_ids' => $customer_type_ids_str[$i],

				'category_ids' => $category_ids,

				'button_text' => $button_text,

				'link_button_to' => $link_button_to,

				'updated_at' => time(),

				'ip' => $_SERVER['REMOTE_ADDR']

			);



			if (!empty($_FILES['customFil']['name'][$i])) {



				$filename_bimg = $_FILES['customFil']['name'][$i];

				$ext = pathinfo($filename_bimg, PATHINFO_EXTENSION);

				$file_name = $_POST['block_id'] . '-banner-' . time() . '-' . $i . '.' . $ext;



				$config2["upload_path"] = SIS_SERVER_PATH . '/' . 'uploads/banners/';

				$config2["allowed_types"] = 'jpg|jpeg|JPG|JPEG|gif|GIF|png|PNG';

				$config2['max_size']			= 1024 * 5;

				$config2['max_width']		= '0';

				$config2['max_height']		= '0';

				$config2['overwrite']		= FALSE;

				$config2['encrypt_name']		= TRUE;

				$config2['file_name'] = $file_name;



				$this->load->library('upload', $config2);



				$this->upload->initialize($config2);



				if ($_FILES['customFil']['tmp_name'][$i] !== "") {

					$_FILES["gfile"]["name"] = $attachment_name = $_FILES["customFil"]["name"][$i];

					$_FILES["gfile"]["type"] = $_FILES["customFil"]["type"][$i];

					$_FILES["gfile"]["tmp_name"] = $_FILES["customFil"]["tmp_name"][$i];

					$_FILES["gfile"]["error"] = $_FILES["customFil"]["error"][$i];

					$_FILES["gfile"]["size"] = $_FILES["customFil"]["size"][$i];



					if ($_FILES["gfile"]["size"] > 5 * 1048576) {

						$arrResponse  = array('status' => 400, 'message' => "Limit exceeds above 5 MB for " . $_FILES["gfile"]["name"]);

						echo json_encode($arrResponse);

						exit;

					}



					$this->load->library('upload', $config2);



					if (!$this->upload->do_upload('gfile')) {

						echo $this->upload->display_errors();

						echo json_encode(array('flag' => 0, 'msg' => "error occured while uploading image ! "));

						exit;

					}



					$uploadData = $this->upload->data();



					$data['banner_image'] = $uploadData['file_name'];

				}

			}



			if (isset($_POST['bannerID'][$i]) && $_POST['bannerID'][$i] !== '') {

				$where_arr = ['id' => $_POST['bannerID'][$i]];

				$this->WebshopModel->updateNewData('banners', $where_arr, $data);

			} else {

				$this->WebshopModel->insertData('banners', $data);

			}

		}



		$redirect = base_url('webshop/static-blocks');



		echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

		exit;

	}



	public function submitHomeblock()

	{

		$fbc_user_id = $this->session->userdata('LoginID');

		$shop_id = $this->session->userdata('ShopID');



		foreach ($_POST['imageName'] as $key => $value) {

			if (empty(trim($value))) {

				echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory field"));

				exit;

			}

		}



		for ($i = 0; $i < 2; $i++) {



			$heading = isset($_POST['bannerHeading'][$i]) ? $this->CommonModel->custom_filter_input($_POST['bannerHeading'][$i]) : '';

			$button_text = isset($_POST['buttonText'][$i]) ? $this->CommonModel->custom_filter_input($_POST['buttonText'][$i]) : '';

			$link_button_to = isset($_POST['buttonLink'][$i]) ? $this->CommonModel->custom_filter_input($_POST['buttonLink'][$i]) : '';



			$data = array(

				'static_block_id' => $_POST['block_id'],

				'position' => $_POST['position'][$i],

				'heading' => $heading,

				'type' => $_POST['homeblockType'][$i],

				'button_text' => $button_text,

				'link_button_to' => $link_button_to,

				'updated_at' => time(),

				'ip' => $_SERVER['REMOTE_ADDR']

			);



			$dfileName = $_POST['imageName'][$i];

			if (!empty($_FILES['customFil']['name'][$i])) {

				$filename_bimg = $_FILES['customFil']['name'][$i];

				$ext = pathinfo($filename_bimg, PATHINFO_EXTENSION);

				$file_name = $fbc_user_id . '-' . $_POST['block_id'] . '-banner-' . time() . '-' . $i . '.' . $ext;



				$config2["upload_path"] = SIS_SERVER_PATH . '/' . 'uploads/banners/';

				$config2["allowed_types"] = 'jpg|jpeg|JPG|JPEG|gif|GIF|png|PNG';

				$config2['max_size']			= 1024 * 5;

				$config2['max_width']		= '0';

				$config2['max_height']		= '0';

				$config2['overwrite']		= FALSE;

				$config2['encrypt_name']		= TRUE;

				$config2['file_name'] = $file_name;



				$this->load->library('upload', $config2);



				$this->upload->initialize($config2);



				if ($_FILES['customFil']['tmp_name'][$i] !== "") {

					$_FILES["gfile"]["name"] = $attachment_name = $_FILES["customFil"]["name"][$i];

					$_FILES["gfile"]["type"] = $_FILES["customFil"]["type"][$i];

					$_FILES["gfile"]["tmp_name"] = $_FILES["customFil"]["tmp_name"][$i];

					$_FILES["gfile"]["error"] = $_FILES["customFil"]["error"][$i];

					$_FILES["gfile"]["size"] = $_FILES["customFil"]["size"][$i];



					if ($_FILES["gfile"]["size"] > 5 * 1048576) {

						$arrResponse  = array('status' => 400, 'message' => "Limit exceeds above 5 MB for " . $_FILES["gfile"]["name"]);

						echo json_encode($arrResponse);

						exit;

					}



					$this->load->library('upload', $config2);



					if (!$this->upload->do_upload('gfile')) {

						echo $this->upload->display_errors();

						echo json_encode(array('flag' => 0, 'msg' => "error occured while uploading image ! "));

						exit;

					}



					$uploadData = $this->upload->data();



					$data['banner_image'] = $uploadData['file_name'];

				}

			}



			if (isset($_POST['bannerID'][$i]) && $_POST['bannerID'][$i] != '') {

				$where_arr = array('id' => $_POST['bannerID'][$i]);

				$this->WebshopModel->updateNewData('banners', $where_arr, $data);

			} else {

				$this->WebshopModel->insertData('banners', $data);

			}

		}



		$redirect = base_url('webshop/static-blocks');

		echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

		exit;

	}





	public function deleteBanner()

	{

		$fbc_user_id = $this->session->userdata('LoginID');

		$shop_id = $this->session->userdata('ShopID');

		$shop_upload_path = 'shop' . $shop_id;



		$blockID = $_POST['blockID'];

		if ($blockID) {

			$bannerData = $this->WebshopModel->getSingleDataByID('banners', array('id' => $blockID), '*');



			$where_arr = array('id' => $blockID);

			$rowAffected = $this->WebshopModel->deleteData('banners', $where_arr);

			// $rowAffected = 1;

			if ($rowAffected) {



				try {

					$this->s3_filesystem->delete('banner/' . $bannerData->banner_image);

				} catch (Exception $e) {

				}



				$redirect = base_url('webshop/static-blocks');

				echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

				exit();

			} else {

				echo json_encode(array('flag' => 0, 'msg' => "nothing to delete!"));

				exit;

			}

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "nothing to delete!"));

			exit;

		}

	}





	public function webshopPayment()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration/read', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$data['side_menu'] = 'webshopPayment';

		$data['PageTitle'] = 'Payement';

		// $data['owner_detail']= $this->WebshopModel->getOwener($_SESSION['ShopOwnerId']);

		// $data['shopData']= $this->WebshopModel->getShopData($_SESSION['ShopOwnerId'],$_SESSION['ShopID']);

		// $data['owner_country'] = $this->CommonModel->get_country_name_by_code($data['shopData']->country_code);

		$data['get_payment_details'] = $this->WebshopModel->get_payementgateways('IN');

		$this->load->view('webshop/webshop_payment.php', $data);

		// print_r_custom($data['get_payment_details']);die();



	}





	public function get_gatewayDetails($id = false)

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */



		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		if (isset($id)) {

			$data['side_menu'] = 'webshopPayment';

			$data['PageTitle'] = 'Payement Details';

			// $data['owner_detail']= $this->WebshopModel->getOwener($_SESSION['ShopOwnerId']);

			// $data['shopData']= $this->WebshopModel->getShopData($_SESSION['ShopOwnerId'],$_SESSION['ShopID']);

			// $data['owner_country'] = $this->CommonModel->get_country_name_by_code($data['shopData']->country_code);



			$data['owner_email'] = $this->CommonModel->getSingleDataByID('webshop_details', array('id' => '1'), 'site_contact_email'); // new added

			$data['get_gateway_details'] = $this->WebshopModel->get_gateway_details($id);

			$data['shop_gateway_credentials'] = $this->WebshopModel->shop_gateway_credentials($id);

			$data['gateway_get_id'] = $id; //new added

			if ($id == 6) {

				$data['connect_account_id'] = '';

				$data['account_status'] = '';

				$data['checkout_session_completed_webhook_key'] = '';

				$data['SecretKey'] = '';

				$data['stripe_account_status'] = '';

				$data['webshopPaymentsStripe'] = $webshopPaymentsStripe = $this->CommonModel->getSingleShopDataByID('webshop_payments_stripe', array('payment_id' => $id), '*');

				if (isset($data['webshopPaymentsStripe'])) {

					if (isset($webshopPaymentsStripe->connect_account_id)) {

						$data['connect_account_id'] = $webshopPaymentsStripe->connect_account_id;

						$data['account_status'] = $webshopPaymentsStripe->status;

						$data['stripe_account_status'] = $this->stripe_account_retrive($webshopPaymentsStripe->connect_account_id);

						if (isset($data['stripe_account_status']) && $data['stripe_account_status'] == 1) {

							$update_data = array(

								'is_able_to_enable' => 1,

								'account_return_status' => 1

							);

							$where_arr = array('payment_id ' => $id);

							$update_id = $this->WebshopModel->updateData('webshop_payments_stripe', $update_data, $where_arr);

						}



						$webshop_payments = $this->CommonModel->getSingleShopDataByID('webshop_payments', array('payment_id' => $id), 'gateway_details');

						if (isset($webshop_payments) && isset($webshop_payments->gateway_details) && !empty($webshop_payments->gateway_details)) {

							$webshop_payments_data = json_decode($webshop_payments->gateway_details);

							$data['checkout_session_completed_webhook_key'] = $webshop_payments_data->checkout_session_completed_webhook_key;

							$data['SecretKey'] = $webshop_payments_data->key;

						} else if (isset($data['webshopPaymentsStripe']) && !empty($data['webshopPaymentsStripe'])) {

							$webhook_create_response_data = json_decode($data['webshopPaymentsStripe']->webhook_create_response);

							if (isset($webhook_create_response_data) && !empty($webhook_create_response_data)) {

								if ($webhook_create_response_data->status == 'enabled') {

									$data['checkout_session_completed_webhook_key'] = $webhook_create_response_data->id;

									$data['SecretKey'] = $webhook_create_response_data->secret;

								} else {

									$data['checkout_session_completed_webhook_key'] = '';

									$data['SecretKey'] = '';

								}

							}

						} else {

							$data['checkout_session_completed_webhook_key'] = '';

							$data['SecretKey'] = '';

						}

					}

				}

			}

			$this->load->view('webshop/webshop_payment_details.php', $data);

		} else {

			echo "ID Not Found!";

		}

	}



	public function store_gateway_credentials()

	{



		$time = time();



		if (isset($_POST)) {

			$gateway_cred = array();

			foreach ($_POST as $key => $val) {

				if ($key != 'type_details' && $key != 'integration_with_us' && $key != 'parent_id' && $key != 'display_name' && $key != 'message') {

					$gateway_cred[$key] = $val;

				}

			}







			$gateway_details =  json_encode($gateway_cred);



			if (empty($gateway_cred)) {

				$gateway_details = "";

			} else {

				// $gateway_details = "else";

			}



			$check_existing = $this->WebshopModel->check_existing_id($_POST['parent_id']);

			if ($check_existing) {

				$update_array = array(

					"payment_id" => $_POST['parent_id'],

					"status" => 1,

					"integrate_with_ws" => $_POST['integration_with_us'],

					"display_name" => $_POST['display_name'],

					"message" => $_POST['message'],

					"payment_type_details" =>  $_POST['type_details'],

					"gateway_details" => $gateway_details,

					"updated_at" => $time

				);

				$update_shop_gateway = $this->WebshopModel->update_shop_gateway($update_array, $_POST['parent_id']);

				if ($update_shop_gateway) {

					echo json_encode(array('flag' => 1, 'msg' => "Success"));

					exit();

				} else {

					echo json_encode(array('flag' => 0, 'msg' => "Failed to Create gateway."));

					exit();

				}

			} else {

				$insert_array = array(

					"payment_id" => $_POST['parent_id'],

					"status" => 1,

					"payment_type_details" =>  $_POST['type_details'],

					"integrate_with_ws" => $_POST['integration_with_us'],

					"display_name" => $_POST['display_name'],

					"message" => $_POST['message'],

					"gateway_details" => $gateway_details,

					"created_at" => $time,

					"created_by" => $_SESSION['LoginID'],

					"ip" => $_SERVER['REMOTE_ADDR']

				);

				$insert_shop_gateway = $this->WebshopModel->insert_shop_gateway($insert_array);

				if ($insert_shop_gateway) {

					echo json_encode(array('flag' => 1, 'msg' => "Success"));

					exit();

				} else {

					echo json_encode(array('flag' => 0, 'msg' => "Failed to Create gateway."));

					exit();

				}

			}

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "Please post data"));

			exit();

		}

	}



	public function integrate_with_us()

	{

		if (isset($_POST)) {



			$update_integrate  = $this->WebshopModel->update_integration($_POST['id'], $_POST['integrate']);

			if ($update_integrate) {

				echo json_encode(array('flag' => 1, 'msg' => "Success"));

				exit();

			} else {

				echo json_encode(array('flag' => 0, 'msg' => "Failed to Update."));

				exit();

			}

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "Please post data"));

			exit();

		}

	}





	public function resizeImageProfile($banner, $homebanner, $categorybanner)

	{

		$source_path = $banner;

		$config = array(

			//header-pic

			array(

				'image_library' => 'gd2',

				'source_image' => $source_path,

				'new_image' => $homebanner,

				'maintain_ratio' => FALSE,

				'width' => 1920,

				'height' => 1080

			),

			//header-pic

			array(

				'image_library' => 'gd2',

				'source_image' => $source_path,

				'new_image' => $categorybanner,

				'maintain_ratio' => FALSE,

				'width' => 1920,

				'height' => 370

			),

		);

		$this->load->library('image_lib', $config);

		foreach ($config as $item) {

			$this->image_lib->initialize($item);

			if (!$this->image_lib->resize()) {

				echo json_encode(array('flag' => 0, 'msg' => $this->image_lib->display_errors()));

				exit();

			}

			$this->image_lib->clear();

		}

	}



	public function webshopProductBlocks()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */



		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration/read', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$data['side_menu'] = 'webshopProductBlocks';

		$data['productBlocks'] = $this->WebshopModel->getProductBlocks();

		$data['PageTitle'] = 'Product Blocks';

		$this->load->view('webshop/webshop_product_blocks', $data);

	}



	public function AssignProductBlocks()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */



		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$blockID = $this->uri->segment(3);

		$data['pBlock'] = $pBlock = $this->WebshopModel->getSingleDataByID('products_block_master', array('id' => $blockID), '*');

		$data['productList'] = $this->WebshopModel->getProductBlockList($pBlock->block_identifier);

		$data['selectedProductList'] = $this->WebshopModel->getSelectedProductBlocks($blockID);

		$data['side_menu'] = 'webshopProductBlocks';

		$data['PageTitle'] = 'Assign Product Blocks';

		$this->load->view('webshop/assign_product_blocks', $data);

	}



	public function submitProductBlock()

	{

		//echo "<pre>"; print_r($_POST); exit;

		if (isset($_POST) && $_POST != '') {

			if (empty($_POST['product_master_id']) || empty($_POST['productList'])) {

				echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

				exit;

			} else {

				$ProductList_arr = isset($_POST['productList']) ? $_POST['productList'] : array();

				if (isset($ProductList_arr)) {

					$ProductList = implode(',', $ProductList_arr);

					$ProductListing = ',' . $ProductList . ',';

				}



				$master_id = $_POST['product_master_id'];



				if (isset($_POST['product_block_id']) && $_POST['product_block_id'] != "") {

					//echo "update";

					$where_arr = array('pb_master_id' => $master_id);

					$updatedata = array(

						'assigned_products' => $ProductListing,

						'updated_at' => time(),

						'ip' => $_SERVER['REMOTE_ADDR']

					);

					$productblock_id = $this->WebshopModel->updateNewData('products_block_details', $where_arr, $updatedata);

				} else {

					//echo "insert";

					$insertdata = array(

						'pb_master_id' => $master_id,

						'assigned_products' => $ProductListing,

						'created_by' => $_SESSION['LoginID'],

						'created_at' => time(),

						'ip' => $_SERVER['REMOTE_ADDR']

					);

					$productblock_id = $this->WebshopModel->insertData('products_block_details', $insertdata);

				}



				if ($productblock_id) {

					$redirect = base_url('webshop/product-blocks');

					echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

					exit();

				} else {

					echo json_encode(array('flag' => 0, 'msg' => "went something wrong!"));

					exit;

				}

			}

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

			exit;

		}

	}





	public function webshopContactUsRequests()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/contact_us_requests',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/contact_us_requests/read', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}

		$data['side_menu'] = 'webshopContactUsRequests';

		$data['contactUsRequests'] = $this->WebshopModel->getContactUsRequests();

		$data['PageTitle'] = 'Contact Us Requests';

		$this->load->view('webshop/webshop_contact_us', $data);

	}



	public function edit_contactus_text()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/contact_us_requests/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}

		$data['side_menu'] = 'webshopContactUsRequests';

		$data['PageTitle'] = 'Edit Contact Us Text';

		$data['shop_id'] = $shop_id = $this->session->userdata('ShopID');

		$data['contact_us_info'] = $this->CommonModel->get_webshop_texts();

		$this->load->view('webshop/edit_contactus_text', $data);

	}



	public function openEditLangTranslate()

	{

		if (isset($_POST['id']) && $_POST['id'] != '') {

			$data['id'] = $id = $_POST['id'];

			$data['code'] = $code = $_POST['code'];

			$data['ContactUsTitle'] = 'Contact Us Text';



			$data['ContactUs'] = $this->WebshopModel->getContactUsDetails($id);

			$data['codeName'] = $codeName = $this->Multi_Languages_Model->getCodeName($code);

			$data['getContactUsTrans'] =  $this->WebshopModel->getContactUsTrans($id, $code);

			$View = $this->load->view('webshop/contactus_trans', $data, true);

			$this->output->set_output($View);

		} else {

			echo "error";

			exit;

		}

	}



	public function SaveContactTranslate()

	{

		$fbc_user_id = $_SESSION['LoginID'];

		if (isset($_POST) && $_POST != '') {

			$id = $_POST['hidden_contact_id'];

			$code = $_POST['code'];

			$checkContactUs = $this->WebshopModel->countContactUs($id, $code);

			if ($checkContactUs > 0) {

				$where_arr = array('text_id' => $id, 'lang_code' => $code);

				$updatetdata = array(

					'message' => $_POST['contact_us_message'],

					'message2' => $_POST['contact_us_message_block2'],

					'message3' => $_POST['contact_us_message_block3'],

					'text_id' => $id,

					'lang_code' => $_POST['code'],

					'updated_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR'],

				);

				$vat_id = $this->WebshopModel->updateMenuData('multi_lang_website_texts', $where_arr, $updatetdata);

				echo json_encode(array('flag' => 1, 'msg' => "Contact Us Translation Updated Successfully."));

				exit();

			} else {

				$insertdata = array(

					'message' => $_POST['contact_us_message'],

					'message2' => $_POST['contact_us_message_block2'],

					'message3' => $_POST['contact_us_message_block3'],

					'text_id' => $id,

					'lang_code' => $_POST['code'],

					'created_at' => time(),

					'created_by' => $fbc_user_id,

					'ip' => $_SERVER['REMOTE_ADDR'],

				);

				$this->WebshopModel->insertData('multi_lang_website_texts', $insertdata);

				echo json_encode(array('flag' => 1, 'msg' => "Contact Us Add Translation Successfully."));

				exit();

			}

		}

	}



	public function submit_contactus_text()

	{

		$enable_email = 0;

		$enable_phone = 0;

		$enable_address = 0;

		if (isset($_POST['enalble_email'])) {

			$enable_email = 1;

		}

		if (isset($_POST['enable_phone'])) {

			$enable_phone = 1;

		}

		if (isset($_POST['enable_address'])) {

			$enable_address = 1;

		}

		if (isset($_POST['row_id']) && $_POST['row_id'] != '') {

			$update_data = array(

				'contact_message' => isset($_POST['message']) ? $_POST['message'] : '',

				'contact_message2' => isset($_POST['message_block2']) ? $_POST['message_block2'] : '',

				'contact_message3' => isset($_POST['message_block3']) ? $_POST['message_block3'] : '',

				'contact_email_enabled' => $enable_email,

				'contact_email' => isset($_POST['email']) ? $_POST['email'] : '',

				'contact_phone_enabled' => $enable_phone,

				'contact_phone' => isset($_POST['phone']) ? $_POST['phone'] : '',

				'contact_address_enabled' => $enable_address,

				'contact_address' => isset($_POST['main_office']) ? $_POST['main_office'] : '',



			);

			$where_arr = array('id' => $_POST['row_id']);

			$update_id = $this->WebshopModel->updateData('website_texts', $update_data, $where_arr);

			if ($update_id) {

				$redirect = base_url('webshop/edit-contact-us-text');

				echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

				exit();

			} else {

				echo json_encode(array('flag' => 0, 'msg' => "Something went wrong!"));

				exit;

			}

		} else {



			$insertdata = array(

				'contact_message' => isset($_POST['message']) ? $_POST['message'] : '',

				'contact_message2' => isset($_POST['message_block2']) ? $_POST['message_block2'] : '',

				'contact_message3' => isset($_POST['message_block3']) ? $_POST['message_block3'] : '',

				'contact_email_enabled' => $enable_email,

				'contact_email' => isset($_POST['email']) ? $_POST['email'] : '',

				'contact_phone_enabled' => $enable_phone,

				'contact_phone' => isset($_POST['phone']) ? $_POST['phone'] : '',

				'contact_address_enabled' => $enable_address,

				'contact_address' => isset($_POST['main_office']) ? $_POST['main_office'] : '',



			);



			$insert_id = $this->WebshopModel->insertData('website_texts', $insertdata);

			if ($insert_id) {

				$redirect = base_url('webshop/edit-contact-us-text');

				echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

				exit();

			} else {

				echo json_encode(array('flag' => 0, 'msg' => "Something went wrong!"));

				exit;

			}

		}

	}



	function viewContactUsMessage()

	{

		$id = $_POST['id'];

		// print_r($_POST);

		// die();

		$ViewCMessage = $this->WebshopModel->get_viewContactUsMessage($id);



		echo json_encode(array('flag' => 1, 'data' => $ViewCMessage));

		exit;

	}



	public function webshopDiscounts()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/discounts/read', $this->session->userdata('userPermission'))) {

				// redirect('dashboard');

			}

		}



		$page_url = $this->uri->segment(2);



		$discount_type = explode("-", $page_url);

		$data['side_menu'] = 'webshop';



		if ($discount_type[0] == 'catalogue') {

			$data['current_tab'] = 'catDiscount';

			$data['PageTitle'] = 'Catalogue Discounts';

			$data['discount_type'] = $discount_type[0];

			$data['add_discount_link'] = base_url('webshop/catalogue-discounts/add');

		} else if ($discount_type[0] == 'product') {

			$data['current_tab'] = 'prodDiscount';

			$data['PageTitle'] = 'Product Discounts';

			$data['discount_type'] = $discount_type[0];

			$data['add_discount_link'] = base_url('webshop/product-discounts/add');

		} else if ($discount_type[0] == 'coupon') {

			$data['current_tab'] = 'cpCode';

			$data['PageTitle'] = 'Coupon Discounts';

			$data['discount_type'] = $discount_type[0];

			$data['add_discount_link'] = base_url('webshop/coupon-discounts/add');

		} else if ($discount_type[0] == 'email') {



			$data['current_tab'] = 'emlCoupon';

			$data['PageTitle'] = 'Email Coupon';

			$data['discount_type'] = $discount_type[0];

			$data['add_discount_link'] = base_url('webshop/email-coupon-discounts/add');

		}

		$this->load->view('webshop/discount/webshop_discount_list', $data);

	}



	public function couponCodeAjaxLoading()

	{

		$discount_type = $this->input->post('discount_type');

		if ($discount_type == 'catalogue') {

			$type = 1;

		} else if ($discount_type == 'product') {

			$type = 2;

		} else if ($discount_type == 'coupon') {

			$type = 3;

		} else if ($discount_type == 'email') {

			$type = 4;

		}



		$webshopDiscountList = $this->WebshopModel->get_datatables_discount_coupon_code($type);

		$data = array();

		foreach ($webshopDiscountList as $readData) {

			if ($readData->type == 1) {

				$edit_discount_link = 'webshop/catalogue-discounts/edit';

			} else if ($readData->type == 2) {

				$edit_discount_link = 'webshop/product-discounts/edit';

			} else if ($readData->type == 3) {

				$edit_discount_link = 'webshop/coupon-discounts/edit';

			} else if ($readData->type == 4) {

				$edit_discount_link = 'webshop/email-coupon-discounts/edit';

			}



			if ($readData->coupon_type == 0) {

				$coupon_type = 'Discount';

			} else {

				$coupon_type = 'Voucher';

			}

			// $start_date = date('d/m/y', strtotime($readData->start_date));

			// $end_date = date('d/m/y', strtotime($readData->end_date));



			$row  = array();

			$row[] = $readData->coupon_code;

			$row[] = $readData->name;

			$row[] = $coupon_type;

			$row[] = $readData->start_date;

			$row[] = $readData->end_date;

			$row[] = $readData->status;

			$row[] = '<a class="link-purple" href="' . base_url() . $edit_discount_link . '/' . $readData->rule_id . '">View</a>';

			$data[] = $row;

		}

		$output = array(

			"draw" => $_POST['draw'],

			"recordsTotal" => $this->WebshopModel->CountGetCouponCodeDiscountList($type),

			"recordsFiltered" => $this->WebshopModel->FiltteredDiscountCouponCode($type),

			"data" => $data,

		);

		echo json_encode($output);

		exit;

	}









	public function add_salesrule_discount_detail()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/discounts/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$fbc_user_id = $this->session->userdata('LoginID');

		$data['side_menu'] = 'webshop';



		$page_url = $this->uri->segment(2);

		$discount_type = explode("-", $page_url);



		$data['discount_type'] = $discount_type[0];

		if ($discount_type[0] == 'catalogue') {

			$data['current_tab'] = 'catDiscount';

			$data['PageTitle'] = 'Catalogue Discounts';

		} else if ($discount_type[0] == 'product') {

			$data['current_tab'] = 'prodDiscount';

			$data['PageTitle'] = 'Product Discounts';

		}



		$data['customer_type'] = $this->WebshopModel->getCustomerTypeMaster();

		$data['catData'] = $this->WebshopModel->getB2BCatDetails();



		if (empty($_POST)) {

			$data['pg_type'] = $pg_type = $this->uri->segment(3);

			if ($pg_type == 'edit') {

				$ruleID = $this->uri->segment(4);

				$data['salesruleData'] = $salesruleData = $this->WebshopModel->getDiscountDetailsById($ruleID);

				$catalogData = $this->WebshopModel->getSingleDataByID('salesrule', array('rule_id' => $ruleID), '*');

				if ($catalogData == '') {

					redirect('dashboard');

				}

				if (isset($salesruleData) && $salesruleData != '') {

					$data['cat_arr'] = explode(",", $salesruleData->apply_on_categories);

					$data['cust_typ_arr'] = explode(",", $salesruleData->apply_to);

				}

			}



			$this->load->view('webshop/discount/add_discount_details', $data);

		} else {

			$checkedCatArr = isset($_POST['checked_cat']) ? $_POST['checked_cat'] : array();

			if (is_array($checkedCatArr) && count($checkedCatArr) <= 0) {

				echo json_encode(array('flag' => 0, 'msg' => "Please seclect at least one Category"));

				exit;

			}

			if (empty($_POST['discount_name']) || empty($_POST['coupon_code']) || empty($_POST['start_date']) || empty($_POST['end_date']) || empty($_POST['discount_amnt'])) {

				echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory fields!"));

				exit;

			} else {

				$discount_name = $this->CommonModel->custom_filter_input($_POST['discount_name']);

				$coupon_code = $this->CommonModel->custom_filter_input($_POST['coupon_code']);

				$disc_status = $this->CommonModel->custom_filter_input($_POST['disc_status']);

				$apply_percent = $this->CommonModel->custom_filter_input($_POST['apply_percent']);

				$discount_amnt = $this->CommonModel->custom_filter_input($_POST['discount_amnt']);



				$apply_to = isset($_POST['apply_to']) ? $_POST['apply_to'] : array();



				$description = $_POST['description'];



				$start_date = (isset($_POST['start_date']) && $_POST['start_date'] != '') ? $_POST['start_date'] : '';

				$end_date = (isset($_POST['end_date']) && $_POST['end_date'] != '') ? $_POST['end_date'] : '';



				$start_date = date('Y-m-d', strtotime($start_date));

				$end_date = date('Y-m-d', strtotime($end_date));



				if (is_array($checkedCatArr) && count($checkedCatArr) > 0) {

					$subcat_ids = implode(",", $checkedCatArr);

				}

				if (is_array($apply_to) && count($apply_to) > 0) {

					$apply_to_customer = implode(",", $apply_to);

				}



				$curr_discount_type = $_POST['current_discount_type'];

				$rules_id = $_POST['rules_id'];



				if ($rules_id != '') {

					//Update

					$where_arr = array('rule_id' => $rules_id);

					$update = array(

						'name' => $discount_name,

						'description' => $description,

						'start_date' => $start_date,

						'end_date' => $end_date,  //selling price

						'status' => $disc_status,

						'apply_type' => $apply_percent,

						'discount_amount' => $discount_amnt,

						'apply_to' => $apply_to_customer,

						'apply_on_categories' => $subcat_ids,

						'updated_at' => time(),

						'ip' => $_SERVER['REMOTE_ADDR']

					);



					$rowAffected = $this->WebshopModel->updateNewData('salesrule', $where_arr, $update);

					if ($rowAffected) {

						$update_coupon = array(

							'coupon_code' => $coupon_code,

							'updated_at' => time(),

							'ip' => $_SERVER['REMOTE_ADDR']

						);

						$row_Affected = $this->WebshopModel->updateNewData('salesrule_coupon', $where_arr, $update_coupon);



						if ($curr_discount_type == 'product') {

							$redirect = base_url('webshop/product-discount-list/' . $rules_id);

						} else {

							$redirect = base_url('webshop/catalogue-discounts');

						}

						echo json_encode(array('flag' => 1, 'msg' => "Updated successfully.", 'discountType' => $curr_discount_type, 'redirect' => $redirect));

						exit;

					} else {

						echo json_encode(array('flag' => 0, 'msg' => "nothing to update!"));

						exit;

					}

				} else {

					$checkedCatArr = isset($_POST['checked_cat']) ? $_POST['checked_cat'] : array();

					if (is_array($checkedCatArr) && count($checkedCatArr) <= 0) {

						echo json_encode(array('flag' => 0, 'msg' => "Please seclect at least one Category"));

						exit;

					}

					$type = (isset($curr_discount_type) && $curr_discount_type == 'product') ? 2 : 1;







					$insertdata = array(

						'name' => $discount_name,

						'description' => $description,

						'type' => $type,

						'start_date' => $start_date,

						'end_date' => $end_date,  //selling price

						'status' => $disc_status,

						'apply_type' => $apply_percent,

						'discount_amount' => $discount_amnt,

						'apply_to' => $apply_to_customer,

						'apply_on_categories' => $subcat_ids,

						'created_at' => time(),

						'created_by' => $fbc_user_id,

						'ip' => $_SERVER['REMOTE_ADDR']

					);

					$sales_rule_id = $this->WebshopModel->insertData('salesrule', $insertdata);

					if ($sales_rule_id) {

						$insert_coupon = array(

							'rule_id' => $sales_rule_id,

							'coupon_code' => $coupon_code,

							'created_by' => $fbc_user_id,

							'created_at' => time(),

							'ip' => $_SERVER['REMOTE_ADDR']

						);

						$coupon_id = $this->WebshopModel->insertData('salesrule_coupon', $insert_coupon);

						if ($curr_discount_type == 'product') {

							$redirect = base_url('webshop/product-discount-list/' . $sales_rule_id);

						} else {

							$redirect = base_url('webshop/catalogue-discounts');

						}

						echo json_encode(array('flag' => 1, 'msg' => "Saved successfully.", 'discountType' => $curr_discount_type, 'redirect' => $redirect));

						exit;

					} else {

						echo json_encode(array('flag' => 0, 'msg' => "Something wrong!"));

						exit;

					}

				}

			}

		}

	}



	public function deleteDiscountDetail()

	{

		$rules_id = $_POST['cp_ruleId'];

		$curr_discount_type = $_POST['curr_discount_type'];

		if ($rules_id != '') {

			$where_arr = array('rule_id' => $rules_id);

			$update = array(

				'remove_flag' => 1,

				'updated_at' => time(),

				'ip' => $_SERVER['REMOTE_ADDR']

			);

			$rowAffected = $this->WebshopModel->updateNewData('salesrule', $where_arr, $update);

			if ($rowAffected) {

				if ($curr_discount_type == 'product') {

					$redirect = base_url('webshop/product-discounts');

				} else if ($curr_discount_type == 'coupon') {

					$redirect = base_url('webshop/coupon-discounts');

				} else if ($curr_discount_type == 'email') {

					$redirect = base_url('webshop/email-coupon');

				} else {

					$redirect = base_url('webshop/catalogue-discounts');

				}

				echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

				exit();

			} else {

				echo json_encode(array('flag' => 0, 'msg' => "Something wrong!"));

				exit;

			}

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "Something wrong!"));

			exit;

		}

	}



	public function viewCheckedCatProductList()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/discounts', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$fbc_user_id = $this->session->userdata('LoginID');

		$data['side_menu'] = 'webshop';

		$data['current_tab'] = 'catDiscount';



		$data['PageTitle'] = 'Discount Product List';

		$cat_Id = $this->uri->segment(3);



		$data['productData'] = $productData = $this->WebshopModel->getProductDetailsByCatId($cat_Id);



		$this->load->view('webshop/discount/view_catalogue_product_list', $data);

	}



	public function viewCategoryProductList()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/discounts', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$fbc_user_id = $this->session->userdata('LoginID');

		$data['side_menu'] = 'webshop';

		$data['current_tab'] = 'prodDiscount';

		$data['PageTitle'] = 'Discount Product List';

		$data['rule_id'] = $rule_Id = $this->uri->segment(3);



		$category_ids = $this->WebshopModel->getApplyOnCatByRuleId($rule_Id);

		$productData = array();

		if (isset($category_ids) && !empty($category_ids)) {

			$catIdArr = explode(",", $category_ids->apply_on_categories);

			$data['productIdArr'] = array_filter(explode(",", $category_ids->apply_on_products ?? ''));

			foreach ($catIdArr as $cat_Id) {

				$productData[] = $this->WebshopModel->getProductDetailsByCatId($cat_Id);

			}

		}



		$data['productData'] = $productData;



		$this->load->view('webshop/discount/view_product_product_list', $data);

	}



	function openCatalogueVariantPopup()

	{



		$data['product_id'] = $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';

		$childProduct = $this->WebshopModel->getConfigChildProduct($product_id);

		$childProduct_arr = array();

		if (!empty($childProduct)) {

			foreach ($childProduct as $child) {

				$variants = $this->WebshopModel->getVariants($child['id']);

				foreach ($variants as $vrnt) {

					$child['variant'][] = $vrnt;

				}

				$childProduct_arr[] = $child;

			}

		}



		$data['variantArr'] = $childProduct_arr;



		$View = $this->load->view('webshop/discount/catalogue_product_variant_popup', $data, true);

		$this->output->set_output($View);

	}



	function openProductVariantPopup()

	{



		$data['product_id'] = $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';

		$rule_id = isset($_POST['rule_id']) ? $_POST['rule_id'] : '';



		$category_ids = $this->WebshopModel->getApplyOnCatByRuleId($rule_id);

		if (isset($category_ids) && !empty($category_ids)) {

			$data['productIdArr'] = array_filter(explode(",", $category_ids->apply_on_products));

		}



		$childProduct = $this->WebshopModel->getConfigChildProduct($product_id);

		$childProduct_arr = array();

		if (!empty($childProduct)) {

			foreach ($childProduct as $child) {

				$variants = $this->WebshopModel->getVariants($child['id']);

				foreach ($variants as $vrnt) {

					$child['variant'][] = $vrnt;

				}

				$childProduct_arr[] = $child;

			}

		}



		$data['variantArr'] = $childProduct_arr;



		$View = $this->load->view('webshop/discount/product_variant_popup', $data, true);

		$this->output->set_output($View);

	}



	public function checkCouponCode()

	{

		if (isset($_POST['coupon_code']) &&  $_POST['coupon_code'] != '') {

			if ($_POST['flag'] == 'add') {

				$coupon_code = $_POST['coupon_code'];



				$CC_Exist = $this->WebshopModel->getSingleDataByID('salesrule_coupon', array('coupon_code' => $coupon_code), 'coupon_id,coupon_code');



				if (isset($CC_Exist) && $CC_Exist->coupon_id != '') {

					echo 'false';

					exit;

				} else {

					echo 'true';

					exit;

				}

			} else if ($_POST['flag'] == 'edit') {

				$cid = $_POST['cid'];

				$coupon_code = $_POST['coupon_code'];

				$CC_Exist = $this->WebshopModel->getSingleDataByID('salesrule_coupon', array('coupon_code' => $coupon_code), 'coupon_id,coupon_code');

				if (isset($CC_Exist) && $CC_Exist->coupon_id != $cid) {

					echo 'false';

					exit;

				} else {

					echo 'true';

					exit;

				}

			} else {

				echo 'true';

				exit;

			}

		} else {

			echo 'true';

			exit;

		}

	}



	public function updateCheckedProductList()

	{

		$fbc_user_id = $this->session->userdata('LoginID');



		if (isset($_POST)) {

			$checkedProductArr = isset($_POST['checkedProduct']) ? $_POST['checkedProduct'] : array();

			$rules_id = isset($_POST['rules_id']) ? $_POST['rules_id'] : '';



			if (is_array($checkedProductArr) && count($checkedProductArr) <= 0) {

				echo json_encode(array('flag' => 0, 'msg' => "Please seclect at least one Product", 'product_id' => ''));

				exit;

			}



			$productIdArr = array();



			if (is_array($checkedProductArr) && count($checkedProductArr) > 0) {



				foreach ($checkedProductArr as $key => $val) {

					$product_id = $val;



					$productIds = isset($_POST['conf_simple_product_' . $product_id]) ? $_POST['conf_simple_product_' . $product_id] : $product_id;



					$productIdArr[] = $product_id;

					$productIdArr[] = $productIds;

				}



				$productIdArr = array_filter(array_unique($productIdArr));



				$product_ids = '';

				if (isset($productIdArr) && count($productIdArr) > 0) {

					$product_ids = implode(',', $productIdArr);

					$product_ids = ',' . $product_ids . ',';

				}



				if ($rules_id != '') {

					$where_arr = array('rule_id' => $rules_id);

					$update = array(

						'apply_on_products' => $product_ids,

						'updated_at' => time(),

						'ip' => $_SERVER['REMOTE_ADDR']

					);



					$rowAffected = $this->WebshopModel->updateNewData('salesrule', $where_arr, $update);



					$redirect = base_url('webshop/product-discounts');

					echo json_encode(array('flag' => 1, 'msg' => "Updated successfully", 'redirect' => $redirect));

					exit();

				} else {

					echo json_encode(array('flag' => 0, 'msg' => "Something went wrong! Rule id missing."));

					exit;

				}

			} else {

				echo json_encode(array('flag' => 0, 'msg' => "Please seclect at least one Product"));

				exit;

			}

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "Please seclect at least one Product"));

			exit;

		}

	}



	public function add_salesrule_couponcode_discount_detail()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/discounts',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */



		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/discounts/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$fbc_user_id = $this->session->userdata('LoginID');

		$data['side_menu'] = 'webshop';



		$page_url = $this->uri->segment(2);

		$discount_type = explode("-", $page_url);



		$data['discount_type'] = $discount_type[0];

		$data['current_tab'] = 'cpCode';

		$data['PageTitle'] = 'Coupon Discounts';



		$data['customer_type'] = $this->WebshopModel->getCustomerTypeMaster();



		if (empty($_POST)) {

			$data['pg_type'] = $pg_type = $this->uri->segment(3);

			if ($pg_type == 'edit') {

				$ruleID = $this->uri->segment(4);

				$data['salesruleData'] = $salesruleData = $this->WebshopModel->getDiscountDetailsById($ruleID);

				$CouponCodeData = $this->WebshopModel->getSingleDataByID('salesrule', array('rule_id' => $ruleID), '*');

				if ($CouponCodeData == '') {

					redirect('dashboard');

				}

				if (isset($salesruleData) && $salesruleData != '') {

					$data['cust_typ_arr'] = explode(",", $salesruleData->apply_to);

				}

			}



			$this->load->view('webshop/discount/add_couponcode_discount_details', $data);

		} else {

			if ((isset($_POST['conditions']) && $_POST['conditions'] == 'discount_on_mincartval')) {

				if (empty($_POST['discount_amnt']) || empty($_POST['cart_val'])) {

					echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory fields!"));

					exit;

				}

			} else if ((isset($_POST['conditions']) && $_POST['conditions'] == 'buyx_getyfree')) {

				if (empty($_POST['product_x']) || empty($_POST['product_x_num']) || empty($_POST['product_y']) || empty($_POST['product_y_num'])) {

					echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory fields!"));

					exit;

				}

			} else if ((isset($_POST['conditions']) && $_POST['conditions'] == 'free_sample')) {

				if (empty($_POST['cart_val_free']) || empty($_POST['product_free']) || empty($_POST['product_free_num'])) {

					echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory fields!"));

					exit;

				}

			}

			if (empty($_POST['discount_name']) || empty($_POST['coupon_code']) || empty($_POST['start_date']) || empty($_POST['end_date'])) {

				echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory fields!"));

				exit;

			} else {

				$apply_percent = '';

				$discount_amnt = 0.00;

				$cart_val = 0.00;

				$product_x = '';

				$product_x_num = 0;

				$product_y = '';

				$product_y_num = 0;

				$discount_name = $this->CommonModel->custom_filter_input($_POST['discount_name']);

				$coupon_code = $this->CommonModel->custom_filter_input($_POST['coupon_code']);

				$disc_status = $this->CommonModel->custom_filter_input($_POST['disc_status']);

				$uses_per_coupon = $this->CommonModel->custom_filter_input($_POST['uses_per_coupon']);

				$uses_per_customer = $this->CommonModel->custom_filter_input($_POST['uses_per_customer']);

				$conditions = $this->CommonModel->custom_filter_input($_POST['conditions']);

				$apply_to = isset($_POST['apply_to']) ? $_POST['apply_to'] : array();

				$description = $_POST['description'];



				if ((isset($_POST['conditions']) && $_POST['conditions'] == 'discount_on_mincartval')) {



					$apply_percent = $this->CommonModel->custom_filter_input($_POST['apply_percent']);

					$discount_amnt = $this->CommonModel->custom_filter_input($_POST['discount_amnt']);

					$cart_val = $this->CommonModel->custom_filter_input($_POST['cart_val']);

				} else if ((isset($_POST['conditions']) && $_POST['conditions'] == 'buyx_getyfree')) {



					$product_x = $this->CommonModel->custom_filter_input($_POST['product_x']);

					$product_x_num = $this->CommonModel->custom_filter_input($_POST['product_x_num']);

					$product_y = $this->CommonModel->custom_filter_input($_POST['product_y']);

					$product_y_num = $this->CommonModel->custom_filter_input($_POST['product_y_num']);

				} else if ((isset($_POST['conditions']) && $_POST['conditions'] == 'free_sample')) {

					$cart_val = $this->CommonModel->custom_filter_input($_POST['cart_val_free']);

					$product_y = $this->CommonModel->custom_filter_input($_POST['product_free']);

					$product_y_num = $this->CommonModel->custom_filter_input($_POST['product_free_num']);

				}



				$start_date = (isset($_POST['start_date']) && $_POST['start_date'] != '') ? $_POST['start_date'] : '';

				$end_date = (isset($_POST['end_date']) && $_POST['end_date'] != '') ? $_POST['end_date'] : '';



				$start_date = date('Y-m-d', strtotime($start_date));

				$end_date = date('Y-m-d', strtotime($end_date));



				if (is_array($apply_to) && count($apply_to) > 0) {

					$apply_to_customer = implode(",", $apply_to);

				}



				$coupon_type = 0; //0-Discount

				if ($apply_percent == 'by_fixed') {

					$coupon_type = 1; //1-Voucher

				}



				// $curr_discount_type = $_POST['current_discount_type'];

				$rules_id = $_POST['rules_id'];



				if ($rules_id != '') {

					//Update

					$where_arr = array('rule_id' => $rules_id);

					$update = array(

						'name' => $discount_name,

						'description' => $description,

						'start_date' => $start_date,

						'end_date' => $end_date,  //selling price

						'status' => $disc_status,

						'apply_type' => $apply_percent,

						'coupon_type' => $coupon_type,

						'discount_amount' => $discount_amnt,

						'apply_to' => $apply_to_customer,

						'min_cart_value' => $cart_val,

						'buyx_product' => $product_x,

						'buyx_product_qty' => $product_x_num,

						'gety_product' => $product_y,

						'gety_product_qty' => $product_y_num,

						'usage_per_customer' => $uses_per_customer,

						'usge_per_coupon' => $uses_per_coupon,

						'updated_at' => time(),

						'ip' => $_SERVER['REMOTE_ADDR']

					);



					$rowAffected = $this->WebshopModel->updateNewData('salesrule', $where_arr, $update);

					if ($rowAffected) {

						$update_coupon = array(

							'coupon_code' => $coupon_code,

							'updated_at' => time(),

							'ip' => $_SERVER['REMOTE_ADDR']

						);

						$row_Affected = $this->WebshopModel->updateNewData('salesrule_coupon', $where_arr, $update_coupon);



						// if($curr_discount_type == 'coupon'){

						$redirect = base_url('webshop/coupon-discounts');

						// }

						echo json_encode(array('flag' => 1, 'msg' => "Updated successfully.", 'redirect' => $redirect));

						exit;

					} else {

						echo json_encode(array('flag' => 0, 'msg' => "nothing to update!"));

						exit;

					}

				} else {

					// $type = (isset($curr_discount_type) && $curr_discount_type == 'coupon') ? 3 : 4;



					$insertdata = array(

						'name' => $discount_name,

						'description' => $description,

						'type' => 3,

						'start_date' => $start_date,

						'end_date' => $end_date,  //selling price

						'status' => $disc_status,

						'apply_condition' => $conditions,

						'apply_type' => $apply_percent,

						'coupon_type' => $coupon_type,

						'discount_amount' => $discount_amnt,

						'apply_to' => $apply_to_customer,

						'min_cart_value' => $cart_val,

						'buyx_product' => $product_x,

						'buyx_product_qty' => $product_x_num,

						'gety_product' => $product_y,

						'gety_product_qty' => $product_y_num,

						'usage_per_customer' => $uses_per_customer,

						'usge_per_coupon' => $uses_per_coupon,

						'created_at' => time(),

						'created_by' => $fbc_user_id,

						'ip' => $_SERVER['REMOTE_ADDR']

					);

					$sales_rule_id = $this->WebshopModel->insertData('salesrule', $insertdata);

					if ($sales_rule_id) {

						$insert_coupon = array(

							'rule_id' => $sales_rule_id,

							'coupon_code' => $coupon_code,

							'created_by' => $fbc_user_id,

							'created_at' => time(),

							'ip' => $_SERVER['REMOTE_ADDR']

						);

						$coupon_id = $this->WebshopModel->insertData('salesrule_coupon', $insert_coupon);

						// if($curr_discount_type == 'coupon'){

						$redirect = base_url('webshop/coupon-discounts');

						// }

						echo json_encode(array('flag' => 1, 'msg' => "Saved successfully.", 'redirect' => $redirect));

						exit;

					} else {

						echo json_encode(array('flag' => 0, 'msg' => "Something wrong!"));

						exit;

					}

				}

			}

		}

	}



	function openProductListPopup()

	{

		$data['product_type'] = $product_type = isset($_POST['product_type']) ? $_POST['product_type'] : '';

		$allProducts = $this->WebshopModel->getAllSimpleProductList();



		foreach ($allProducts as $value) {

			$variants = $this->WebshopModel->getVariants($value->id);

			$variant = (object)$variants;



			$cat_name = $this->SellerProductModel->getProductsMaintCategoryNames($value->id);



			$value->cat_name = $cat_name;

			$value->variant = $variant;

			$ProductData[] = $value;

		}

		$data['allProducts'] = $ProductData;

		$View = $this->load->view('webshop/discount/product_list_popup', $data, true);

		$this->output->set_output($View);

	}



	public function add_salesrule_email_discount_detail()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/discounts',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */



		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/discounts/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$fbc_user_id = $this->session->userdata('LoginID');

		$data['side_menu'] = 'webshop';



		$page_url = $this->uri->segment(2);

		$discount_type = explode("-", $page_url);



		$data['discount_type'] = $discount_type[0];

		$data['current_tab'] = 'emlCoupon';

		$data['PageTitle'] = 'Email Coupon';



		//$data['currency_symbol']=(isset($shopData->currency_symbol))?$shopData->currency_symbol:$shopData->currency_code;



		$data['non_editable_field'] = '';

		if (empty($_POST)) {

			$data['pg_type'] = $pg_type = $this->uri->segment(3);

			if ($pg_type == 'edit') {

				$ruleID = $this->uri->segment(4);

				$catalogData = $this->WebshopModel->getSingleDataByID('salesrule', array('rule_id' => $ruleID), '*');

				if ($catalogData == '') {

					redirect('dashboard');

				}

				$data['salesruleData'] = $salesruleData = $this->WebshopModel->getDiscountDetailsById($ruleID);

				$data['DiplayData'] = $DiplayData = $this->WebshopModel->getMultiDataById('salesrule_coupon', array('rule_id' => $ruleID), '');

				$data['non_editable_field'] = 'disabled';

				if (isset($salesruleData) && $salesruleData != '') {

					if ($salesruleData->apply_condition == 'free_sample') {

						$data['free_sample_arr'] = json_decode($salesruleData->free_sample);

					}

				}

			}



			$this->load->view('webshop/discount/add_email_discount_details', $data);

		} else {



			if ($_POST['current_page'] == 'add') {



				if ((isset($_POST['conditions']) && $_POST['conditions'] == 'discount_on_mincartval')) {

					if (empty($_POST['discount_amnt']) || empty($_POST['cart_val'])) {

						echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory fields!"));

						exit;

					}

				} else if ((isset($_POST['conditions']) && $_POST['conditions'] == 'buyx_getyfree')) {

					if (empty($_POST['product_x']) || empty($_POST['product_x_num']) || empty($_POST['product_y']) || empty($_POST['product_y_num'])) {

						echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory fields!"));

						exit;

					}

				} else if ((isset($_POST['conditions']) && $_POST['conditions'] == 'free_sample')) {

					$productFreeArr = isset($_POST['product_free']) ? array_filter($_POST['product_free']) : array();

					$productFreeNumArr = isset($_POST['product_free_num']) ? array_filter($_POST['product_free_num']) : array();



					if (empty($_POST['cart_val_free']) || empty($productFreeArr) || empty($productFreeNumArr)) {

						echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory fields!"));

						exit;

					}

				}



				if ($_POST['cpradio'] == 0 && empty($_POST['coupon_code'])) {

					echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory fields!"));

					exit;

				}

				if (empty($_POST['discount_name']) || empty($_POST['start_date']) || empty($_POST['end_date'])) {

					echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory fields!"));

					exit;

				} else {





					$apply_percent = '';

					$discount_amnt = 0.00;

					$cart_val = 0.00;

					$product_x = '';

					$product_x_num = 0;

					$product_y = '';

					$product_y_num = 0;

					$freeSampleJson = '';

					$discount_name = $this->CommonModel->custom_filter_input($_POST['discount_name']);

					$disc_status = $this->CommonModel->custom_filter_input($_POST['disc_status']);

					$conditions = $this->CommonModel->custom_filter_input($_POST['conditions']);



					if ((isset($_POST['conditions']) && $_POST['conditions'] == 'discount_on_mincartval')) {



						$apply_percent = $this->CommonModel->custom_filter_input($_POST['apply_percent']);

						$discount_amnt = $this->CommonModel->custom_filter_input($_POST['discount_amnt']);

						$cart_val = $this->CommonModel->custom_filter_input($_POST['cart_val']);

					} else if ((isset($_POST['conditions']) && $_POST['conditions'] == 'buyx_getyfree')) {



						$product_x = $this->CommonModel->custom_filter_input($_POST['product_x']);

						$product_x_num = $this->CommonModel->custom_filter_input($_POST['product_x_num']);

						$product_y = $this->CommonModel->custom_filter_input($_POST['product_y']);

						$product_y_num = $this->CommonModel->custom_filter_input($_POST['product_y_num']);

					} else if ((isset($_POST['conditions']) && $_POST['conditions'] == 'free_sample')) {

						$productFreeArr = $_POST['product_free'];

						$productFreeNumArr = $_POST['product_free_num'];

						$cart_val = $this->CommonModel->custom_filter_input($_POST['cart_val_free']);



						$freeSampleArr = array_combine($productFreeArr, $productFreeNumArr);

						$freeSampleJson = json_encode($freeSampleArr);

					}





					$description = $_POST['description'];

					$email_subject = (isset($_POST['email_subject']) && $_POST['email_subject'] != '') ? $_POST['email_subject'] : '';



					$email_coupon_type = $_POST['cpradio'];

					if ($_POST['cpradio'] == 1) {

						$prefix = $_POST['prefix'];

					} else {

						$prefix = '';

					}

					$message = $_POST['email_message'];

					$apply_to = $_POST['apply_to'];

					// $email_array = explode(",", $apply_to);

					$email_array = array_map('trim', explode(',', $apply_to));



					$start_date = (isset($_POST['start_date']) && $_POST['start_date'] != '') ? $_POST['start_date'] : '';

					$end_date = (isset($_POST['end_date']) && $_POST['end_date'] != '') ? $_POST['end_date'] : '';



					$expiry_date = date('j F Y', strtotime($end_date));



					$start_date = date('Y-m-d', strtotime($start_date));

					$end_date = date('Y-m-d', strtotime($end_date));



					$coupon_type = 0; //0-Discount

					if ($apply_percent == 'by_fixed') {

						$coupon_type = 1; //1-Voucher

					}



					$curr_discount_type = $_POST['current_discount_type'];

					/*----------------Send Email to customer--------------------*/

					//$shop_owner = $this->CommonModel->getShopOwnerData($shop_id);

					//$amountwithcurrency=$shop_owner->currency_code." ".$discount_amnt;

					//$shop_name = $shop_owner->org_shop_name;

					$TempVars = array();

					$DynamicVars = array();



					/*---------------- END --------------------*/

					$type = (isset($curr_discount_type) && $curr_discount_type == 'coupon') ? 3 : 4;



					$insertdata = array(

						'name' => $discount_name,

						'description' => $description,

						'type' => $type,

						'coupon_type' => $coupon_type,

						'start_date' => $start_date,

						'end_date' => $end_date,  //selling price

						'status' => $disc_status,

						'apply_condition' => $conditions,

						'apply_type' => $apply_percent,

						'discount_amount' => $discount_amnt,

						'min_cart_value' => $cart_val,

						'buyx_product' => $product_x,

						'buyx_product_qty' => $product_x_num,

						'gety_product' => $product_y,

						'gety_product_qty' => $product_y_num,

						'usage_per_customer' => 1,

						'email_coupon_type' => $email_coupon_type,

						'message' => $message,

						'email_subject' => $email_subject,

						'free_sample' => $freeSampleJson,

						'created_at' => time(),

						'created_by' => $fbc_user_id,

						'ip' => $_SERVER['REMOTE_ADDR']

					);



					$sales_rule_id = $this->WebshopModel->insertData('salesrule', $insertdata);

					if ($sales_rule_id) {

						foreach ($email_array as $value) {

							if ($email_coupon_type == 1) {

								$coupon_code = $prefix . $this->generateCouponCode(10);

							} else {

								$coupon_code = $_POST['coupon_code'];

							}



							$insert_coupon = array(

								'rule_id' => $sales_rule_id,

								'coupon_code' => $coupon_code,

								'coupon_code_prefix' => $prefix,

								'email_address' => $value,

								'created_by' => $fbc_user_id,

								'created_at' => time(),

								'ip' => $_SERVER['REMOTE_ADDR']

							);



							$coupon_id = $this->WebshopModel->insertData('salesrule_coupon', $insert_coupon);



							$this->load->model('WebshopOrdersModel');



							// Send Email

							$to = $value;

							//$webshop_details=$this->CommonModel->get_webshop_details($shop_id);

							$site_logo = '';

							//if(isset($webshop_details)){

							// $shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);

							//}

							$burl = base_url();

							$shop_logo = '';

							$shop_id = '';

							$amountwithcurrency = '';

							$shop_name = '';

							/* $site_logo =  '<a href="'.getWebsiteUrl('',$burl).'" style="color:#1E7EC8;">

								<img  width="200" alt="'.$shop_name.'" border="0" src="'.$shop_logo.'" />

								</a>'; */

							$CommonVars = array($site_logo, $shop_name);

							if ($coupon_type == 1) {

								$templateId = 'email-vouchercode';

								$TempVars = array("##VOUCHERCODE##", "##VOUCHERAMOUNT##", "##VOUCHEREXPIRYDATE##", "##MESSAGE##", '##WEBSHOPNAME##');

								$DynamicVars = array($coupon_code, $amountwithcurrency, $expiry_date, $message, $shop_name);

							} else {

								$templateId = 'email-discountcode';

								$TempVars = array("##DISCOUNTCODE##", "##DISCOUNTEXPIRYDATE##", "##MESSAGE##", "##WEBSHOPNAME##");

								$DynamicVars = array($coupon_code, $expiry_date, $message, $shop_name);

							}



							if (isset($templateId)) {

								/* $emailSendStatusFlag=$this->CommonModel->sendEmailStatus($templateId,'');

								if($emailSendStatusFlag==1){

									$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to,$templateId,$TempVars,$DynamicVars,$email_subject,$CommonVars);

									if($mailSent){

										$where_arr = array('coupon_id'=>$coupon_id);

										$update_coupon = array('email_sent'=>1);

										$row_Affected = $this->WebshopModel->updateNewData('salesrule_coupon',$where_arr,$update_coupon);

									}

								} */

							}

						}

						$redirect = base_url('webshop/email-coupon');

						echo json_encode(array('flag' => 1, 'msg' => "Saved successfully.", 'redirect' => $redirect));

						exit;

					} else {

						echo json_encode(array('flag' => 0, 'msg' => "Something wrong!"));

						exit;

					}

				}

			} else {

				if (empty($_POST['discount_name'])) {

					echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory fields!"));

					exit;

				} else {



					$discount_name = $this->CommonModel->custom_filter_input($_POST['discount_name']);

					$disc_status = $this->CommonModel->custom_filter_input($_POST['disc_status']);

					$description = $_POST['description'];

					$rules_id = $_POST['rules_id'];



					if ($rules_id != '') {

						//Update

						$where_arr = array('rule_id' => $rules_id);

						$update = array(

							'name' => $discount_name,

							'description' => $description,

							'status' => $disc_status,

							'updated_at' => time(),

							'ip' => $_SERVER['REMOTE_ADDR']

						);



						$rowAffected = $this->WebshopModel->updateNewData('salesrule', $where_arr, $update);

						if ($rowAffected) {



							$redirect = base_url('webshop/email-coupon');

							echo json_encode(array('flag' => 1, 'msg' => "Updated successfully.", 'redirect' => $redirect));

							exit;

						} else {

							echo json_encode(array('flag' => 0, 'msg' => "nothing to update!"));

							exit;

						}

					}

				}

			}

		}

	}



	public function webshopSettings()

	{



		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$data['side_menu'] = 'webShop';

		$data['PageTitle'] = 'Webshp - Settings';

		$data['LoginID'] = $LoginID = $this->session->userdata('LoginID');

		$data['webshopID'] = 1;

		$data['settings_info'] = $settings_info = $this->CommonModel->getSingleDataByID('webshop_details', array('id' => 1), 'site_logo,site_name,site_contact_email,site_contact_no,meta_title,meta_keywords,meta_description');

		// $data['default_settings_data'] = $default_settings_data = $this->CommonModel->getSingleDataByID('fbc_users',array('fbc_user_id'=> $fbc_user_id),'email,mobile_no');

		$this->load->view('webshop/settings', $data);

	}



	public function saveWebshopSettings()

	{

		$webshopID = $this->input->post('webshopID');

		$uploadOk = true;



		$data = [

			'site_name' => $this->input->post('sitename'),

			'meta_title' => $this->input->post('meta_title'),

			'meta_keywords' => $this->input->post('meta_keywords'),

			'meta_description' => $this->input->post('meta_description'),

			'site_contact_email' => $this->input->post('site_email'),

			'site_contact_no' => $this->input->post('site_contact_no'),

			'created_by' => $_SESSION['LoginID'],

			'created_at' => time(),

			'ip' => $_SERVER['REMOTE_ADDR']

		];



		// if($_FILES["site_favicon"]["name"])

		// {



		// 	$file_tmp= $_FILES['site_favicon']['tmp_name'];

		// 	$file_name= $_FILES['site_favicon']['name'];



		// 	$type = pathinfo($file_name, PATHINFO_EXTENSION);

		// 	$data_favicon = file_get_contents($file_tmp);

		// 	$base64 = base64_encode($data_favicon);



		// 	$uploadOk = $this->generateFavicon($base64,$shop_id);

		// 	if($uploadOk === false){

		// 		$this->session->set_flashdata('err', "Problem in updating image file.");

		// 	}

		// }



		// if($_FILES["site_logo"]["name"])

		// {

		// 	$site_logo = basename($_FILES["site_logo"]["name"]);



		// 	 $this->encryption->initialize(array('driver' => 'mcrypt'));

		// 	$data['site_logo'] = $this->encryption->encrypt($site_logo);



		//     if(!isset($_FILES["site_logo"]["tmp_name"])) {

		// 		$this->session->set_flashdata('err', "Choose image file to upload.");

		// 	}

		// 	$uploadOk = $this->image_upload->upload_image($_FILES['site_logo'], '', $site_logo);



		// 	if ($uploadOk === false) {

		// 		$this->session->set_flashdata('err', "Problem in updating image file.");

		// 	}

		// }



		if ($uploadOk === true) {

			if ($this->input->post('action') === 'update') {

				$res = $this->CommonModel->updateData('webshop_details', ['id' => $webshopID], $data);

			} else {

				$res = $this->WebshopModel->insertDBdata('webshop_details', $data);

			}

			if ($res === true) {

				$this->session->set_flashdata('true', 'Settings  save successful.');

			} else {

				$this->session->set_flashdata('err', "Sorry, there was an error while saving settings.");

			}

		}



		redirect(base_url('webshop/settings'));

	}



	function generateFavicon($base64, $shop_id)

	{



		$path_url = get_s3_base_url($shop_id);

		$path_url = $path_url . "favicon/";

		$jayParsedAry = [

			"favicon_generation" => [

				"api_key" => FAVICON_API_KEY,

				"master_picture" => [

					"type" => "inline",

					"content" => $base64

				],

				"files_location" => [

					"type" => "path",

					"path" => $path_url

				],

				"favicon_design" => [

					"desktop_browser" => [],

					"ios" => [

						"picture_aspect" => "no_change",

						"startup_image" => [

							"master_picture" => [

								"type" => "inline",

								"content" => $base64

							]

						],

						"assets" => [

							"ios6_and_prior_icons" => false,

							"ios7_and_later_icons" => true,

							"precomposed_icons" => false,

							"declare_only_default_icon" => true

						]

					],

					"windows" => [

						"picture_aspect" => "no_change",

						"assets" => [

							"windows_80_ie_10_tile" => true,

							"windows_10_ie_11_edge_tiles" => [

								"small" => false,

								"medium" => true,

								"big" => true,

								"rectangle" => false

							]

						]

					],

					"firefox_app" => [

						"picture_aspect" => "no_change",

						"manifest" => []

					],

					"android_chrome" => [

						"picture_aspect" => "no_change",

						"manifest" => [],

						"assets" => [

							"legacy_icon" => true,

							"low_resolution_icons" => false

						]

					]

				]

			]

		];



		$faviconData = json_encode($jayParsedAry);



		$curl = curl_init();



		curl_setopt_array($curl, array(

			CURLOPT_URL => FAVICON_API_URL,

			CURLOPT_RETURNTRANSFER => true,

			CURLOPT_ENCODING => '',

			CURLOPT_MAXREDIRS => 10,

			CURLOPT_TIMEOUT => 0,

			CURLOPT_FOLLOWLOCATION => true,

			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

			CURLOPT_CUSTOMREQUEST => 'POST',

			CURLOPT_POSTFIELDS => $faviconData,

			CURLOPT_HTTPHEADER => array(

				'Content-Type: application/json'

			),

		));



		$response = curl_exec($curl);



		curl_close($curl);

		$responseData = json_decode($response, true);



		if ($responseData['favicon_generation_result']['result']['status'] == 'success') {

			$files_urls = $responseData['favicon_generation_result']['favicon']['files_urls'];

			foreach ($files_urls as $url) {

				$file_name = basename($url);

				$this->s3_filesystem->putFile($url, '/favicon/' . $file_name);

			}



			return true;

		} else {

			return false;

		}

	}



	// Generate Random Coupon Code

	function generateCouponCode($length)

	{

		$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

		$string = '';



		for ($i = 0; $i < $length; $i++) {

			$string .= $chars[mt_rand(0, strlen($chars) - 1)];

		}



		return $string;

	}



	public function special_pricing()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/discounts',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */



		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/discounts/read', $this->session->userdata('userPermission'))) {

				// redirect('dashboard');

			}

		}



		$data['side_menu'] = 'webshop';

		$data['current_tab'] = 'specialPricing';

		$data['PageTitle'] = 'Seller Special Pricing';

		$data['add_special_pricing_link'] = base_url('webshop/add-special-pricing');

		$data['bulk_add_special_pricing_link'] = base_url('webshop/bulk-add-special-pricing');

		$this->load->view('webshop/discount/special_pricing_list', $data);

	}



	public function loadspecialpricingajax()

	{

		$data['shop_id'] = $shop_id = $this->session->userdata('ShopID');

		//$data['currency_symbol']=$currency_symbol = $this->CommonModel->getShopCurrency($shop_id);

		$allProducts = $this->WebshopModel->get_datatables_special_prices();

		$Special_product_ids = array_column($allProducts, 'product_id');

		$Customer_type_ids = array_column($allProducts, 'customer_type_id');

		$variant_data_id = array();

		if (!empty($Special_product_ids)  && !empty($Customer_type_ids)) {



			$variants = $this->WebshopModel->getVariantsByID($Special_product_ids);

			$variant_data_id = array_column($variants, 'product_id');

			$this->variants_match = $this->CommonModel->array_group_data($variants, 'product_id');



			$customer_type = $this->WebshopModel->get_customer_type_details($Customer_type_ids);

			$customer_id = array_column($customer_type, 'id');

			$this->customer_type = $this->CommonModel->array_group_data($customer_type, 'id');

		}





		$data = array();

		foreach ($allProducts as $readData) {

			// $variants = $this->WebshopModel->getVariants($readData->product_id);

			// $variant = (Object)$variants;



			$cat_name = $this->SellerProductModel->getProductsMaintCategoryNames($readData->product_id);



			$row = array();

			$row[] = '<input type="checkbox"  name="chk_sp[]" value="' . $readData->id . '" > ';

			$row[] = $readData->sku;

			$row[] = $readData->name;



			$variant = " ";

			if (in_array($readData->product_id, $variant_data_id, true)) {

				$variantValueData = $this->variants_match[$readData->product_id];

				if (isset($variantValueData) && $variantValueData > 0) {

					foreach ($variantValueData as $value) {



						$variant .= $value['attr_name'] . ':' . $value['attr_options_name'] . ',';

					}

					$variant =  rtrim($variant, " , ");

				}

			} else {

				$variant = "-";

			}



			$row[] = $variant;

			$row[] = '&#x20b9; ' . $readData->webshop_price;



			//$customer_type= $this->CustomerModel->get_single_customer_type_details($readData->customer_type_id);



			$Customer_type = "";

			$row[] = '&#x20b9; ' . $readData->special_price;

			$row[] =  date("d-m-Y", $readData->special_price_from);

			$row[] =  date("d-m-Y", $readData->special_price_to);

			$current_date = date("Y-m-d");

			$from_date = date("Y-m-d", $readData->special_price_from);

			$to_date = date("Y-m-d", $readData->special_price_to);

			if ($current_date >= $from_date && $current_date <=  $to_date) {

				$row[] = "Active";

			} elseif ($to_date < $current_date) {

				$row[] = "Inactive";

			} elseif ($from_date > $current_date) {

				$row[] = "Upcomming";

			}



			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/discounts/write', $this->session->userdata('userPermission'))) {

				$row[] = '<a class="link-purple" href="' . base_url() . 'webshop/edit-special-pricing/' . $readData->id . '">View</a>';

			} else {

				$row[] = '<a class="link-purple" href="' . base_url() . 'webshop/edit-special-pricing/' . $readData->id . '">View</a>/ <a class="link-purple trash" data-toggle="modal" data-target="#deleteModalForRow" data-id="' . $readData->id . '">Delete</a>';

			}



			$data[] = $row;

		}



		// echo "<pre>"; print_r($data);// die();

		$output = array(

			"draw" => $_POST['draw'],

			"recordsTotal" => $this->WebshopModel->countspecialpricerecord(),

			"recordsFiltered" => $this->WebshopModel->countfilterspecialprice(),

			"data" => $data,

		);

		//output to json format

		echo json_encode($output);

		exit;

	}





	public function bulk_add_special_pricing()

	{



		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/discounts/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$data['PageTitle'] = 'webshop - CSV Import';

		$data['side_menu'] = 'webshop';

		$data['current_tab'] = 'specialPricing';

		$data['shop_id'] = $shop_id = $this->session->userdata('ShopID');

		$this->load->view('webshop/discount/bulk_add_special_pricing', $data);

	}



	function openbulkuploadpopup()

	{

		$data['PageTitle'] = 'Webshop - CSV Import';

		$data['side_menu'] = 'bulk-add';

		//$data['type']=$type=isset($_POST['type'])?$_POST['type']:'';

		$View = $this->load->view('webshop/discount/bulk_upload_csv', $data, true);

		$this->output->set_output($View);

	}



	public function delete_all_special_pricing()

	{

		$sp_arr = (isset($_POST['chk_sp']) && $_POST['chk_sp'] != '') ? $_POST['chk_sp'] : '';

		if (isset($sp_arr) && $sp_arr != '') {

			foreach ($sp_arr as  $value) {

				$where_arr = array('id' => $value);

				$rowAffected = $this->WebshopModel->deleteData('products_special_prices', $where_arr);

			}

			if ($rowAffected > 0) {

				$redirect = base_url('webshop/special-pricing');

				echo json_encode(array('flag' => 1, 'msg' => "Successfully Deleted Rows", 'redirect' => $redirect));

				exit();

			} else {

				echo json_encode(array('flag' => 0, 'msg' => "nothing to delete!"));

				exit;

			}

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "Please Select Special pricing to delete."));

			exit;

		}

	}



	public function openbulkselectcategory()

	{

		$data['type'] = $type = isset($_POST['type']) ? $_POST['type'] : '';

		if ($data['type'] == 'importAll') {

			$data['PageTitle'] = 'Webshop - CSV Import All ';

			$data['side_menu'] = 'webshop';

		} else {

			$data['PageTitle'] = 'Webshop - CSV Import';

			$data['side_menu'] = 'webshop';

		}

		$View = $this->load->view('webshop/discount/bulk_category', $data, true); //pending

		$this->output->set_output($View);

	}



	public	function checkcsvdata()

	{

		if (empty($_FILES['upload_csv_file']['name'])) {

			$arrResponse = array('status' => 400, 'message' => 'Please upload proper csv file');

			exit(json_encode($arrResponse));

		}



		$ext = pathinfo($_FILES['upload_csv_file']['name'], PATHINFO_EXTENSION);

		if ($ext !== 'csv') {

			exit(json_encode(['status' => 400, 'message' => "Please Upload Files With .CSV Extenion Only."]));

		}



		$csv = Reader::createFromPath($_FILES['upload_csv_file']['tmp_name'], 'r');

		$csv->setHeaderOffset(0);



		if ($csv->count() <= 1) {

			exit(json_encode(['status' => 400, 'message' => 'No rows found in CSV']));

		}



		foreach ($csv as $special_price) {

			$sku = $special_price['sku'];

			$special_price_val = $special_price['special_price'];

			$special_price_from = $special_price['special_price_from'];

			$special_price_to = $special_price['special_price_to'];

			$display_original = $special_price['display_original'];



			if (empty($sku) || empty($special_price_val) || empty($special_price_from) || empty($special_price_to) || empty($display_original)) {

				exit(json_encode(['status' => 400, 'message' => 'Invalid csv file, empty values']));

			}

		}



		$skus = array_unique(iterator_to_array($csv->fetchColumn('sku')));

		$all_skus_exist = $this->WebshopModel->check_all_skus_exist($skus);



		if (!$all_skus_exist) {

			$sku_not_found = $this->WebshopModel->check_which_skus_do_not_exist($skus);

			$sku_str = implode(', ', $sku_not_found);

			exit(json_encode(['status' => 400, 'message' => " SKU  $sku_str not found in database."]));

		}



		$arrResponse = array('status' => 200, 'message' => "Special Pricing sheet validated, Please continue to upload.");

		exit(json_encode($arrResponse));

	}



	function import_special_pricing()

	{

		$fbc_user_id	=	$this->session->userdata('LoginID');



		if (empty($_FILES['upload_csv_file']['name'])) {

			exit(json_encode(['status' => 400, 'message' => 'Please upload valid csv file']));

		}





		$csv = Reader::createFromPath($_FILES['upload_csv_file']['tmp_name'], 'r');

		$csv->setHeaderOffset(0);



		if ($csv->count() <= 1) {

			exit(json_encode(['status' => 400, 'message' => 'No rows found in CSV']));

		}



		$skus = array_unique(iterator_to_array($csv->fetchColumn('sku')));

		$all_skus_exist = $this->WebshopModel->check_all_skus_exist($skus);



		if (!$all_skus_exist) {

			exit(json_encode(['status' => 400, 'message' => 'Please upload valid csv file, not all skus exist']));

		}



		$product_ids = $this->WebshopModel->get_product_ids_from_skus($skus);



		$this->WebshopModel->start_transaction();



		foreach ($csv as $special_price) {

			$sku = $special_price['sku'];

			$special_price_val = $special_price['special_price'];

			$special_price_from = strtotime($special_price['special_price_from']);

			$special_price_to = strtotime($special_price['special_price_to']);

			$display_original = $special_price['display_original'];



			if ($sku === '') {

				continue;

			}



			$product_id = $product_ids[$sku];



			$this->WebshopModel->update_or_insert_special_pricing([

				'product_id' => $product_id,

				'special_price' => $special_price_val,

				'special_price_from' => $special_price_from,

				'special_price_to' => $special_price_to,

				'display_original' => $display_original,

			]);

		}

		$this->WebshopModel->complete_transaction();



		exit(json_encode(['status' => 200, 'message' => 'Special Prices sheet Imported Successfully.']));

	}







	public function downloadproductcsv()

	{

		$special_pricing = $this->WebshopModel->getspecailpricingForCSVImport();

		$sis_export_header = ["sku", "special_price", "special_price_from", "special_price_to", "display_original"];



		$filename = 'SISSpecialPricing-' . time() . '.csv';

		header("Content-Description: File Transfer");

		header("Content-Disposition: attachment; filename=$filename");

		header("Content-Type: application/csv; ");



		// file creation

		$file = fopen('php://output', 'wb');

		fputcsv($file, $sis_export_header);



		if (isset($special_pricing) && count($special_pricing) > 0) {

			foreach ($special_pricing  as $special_price_val) {

				$product_type = $special_price_val->product_type;



				if ($special_price_val->sku === '') {

					continue;

				}



				if ($product_type === 'conf-simple' || $product_type === 'simple') {

					fputcsv($file, [

						$special_price_val->sku,

						$special_price_val->special_price,

						!empty($special_price_val->special_price_from) ? date("d-m-Y", $special_price_val->special_price_from) : '-',

						!empty($special_price_val->special_price_to) ? date("d-m-Y", $special_price_val->special_price_to) : '-',

						$special_price_val->display_original

					]);

				}

			}

		}



		fclose($file);

		exit;

	}



	public function download_all_ProductCSV()

	{

		$sis_export_header = ["sku", "barcode", "name", "launch_date", "variants", "categories", "inventory", "cost_price", "tax_percent", "price", "webshop_price"];





		$filename = 'SISSpecialPricing-' . time() . '.csv';

		header("Content-Description: File Transfer");

		header("Content-Disposition: attachment; filename=$filename");

		header("Content-Type: application/csv; ");

		$file = fopen('php://output', 'wb');

		fputcsv($file, $sis_export_header);

		$special_pricing = $this->WebshopModel->getspecailpricingForALLCSVImport();

		$product_ids = array_column($special_pricing, 'id');

		$variant = $this->WebshopModel->getVariantsMultiple($product_ids);



		$variants_match_ids = array_column($variant, 'product_id');

		$this->variants_match = $this->CommonModel->array_group($variant, 'product_id');





		if (isset($special_pricing) && count($special_pricing) > 0) {

			foreach ($special_pricing  as $special_price_val) {

				$id = $special_price_val->id;

				$variants_new = '';

				if (in_array($special_price_val->id, $variants_match_ids, true)) {

					$variantValueData = $this->variants_match[$special_price_val->id];

					if (isset($variantValueData) && $variantValueData > 0) {

						foreach ($variantValueData as $value1) {

							$variants_new .= $value1->attr_name . ' : ' . $value1->attr_options_name . ' ; ';

						}

						$variants_new =  rtrim($variants_new, " ; ");

					}

				}





				fputcsv($file, [

					$sku = $special_price_val->sku,

					$barcode = $special_price_val->barcode,

					$name = $special_price_val->name,

					$launch_date = (isset($special_price_val->launch_date) && $special_price_val->launch_date != '' && $special_price_val->launch_date) ? date("d-m-Y", $special_price_val->launch_date) : '-',

					$variants_new = $variants_new,

					$cat_name = (isset($special_price_val->cat_name)) ? $special_price_val->cat_name : '-',

					$inventory = $special_price_val->available_qty,

					$cost_price = $special_price_val->cost_price,

					$tax_percent = $special_price_val->tax_percent,

					$selling_price = $special_price_val->price,

					$webshop_price = $special_price_val->webshop_price



				]);

			}

			// print_r_custom($ExportValuesArr);exit;

		}



		fclose($file);

		exit;

	}





	public function add_special_pricing()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/discounts',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */



		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/discounts/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$data['side_menu'] = 'webshop';

		$data['current_tab'] = 'specialPricing';

		$data['PageTitle'] = 'Add Special Pricing';

		$data['shop_id'] = $shop_id = $this->session->userdata('ShopID');

		$allProducts = $this->WebshopModel->getAllProductsData();

		foreach ($allProducts as $value) {

			$ProductData[] = $value;

		}

		$data['product_details'] = $ProductData;

		$data['customer_type_details'] = $this->CustomerModel->get_customer_type_details();

		$data['special_pricing_link'] = base_url('webshop/special-pricing');

		$this->load->view('webshop/discount/add_special_pricing', $data);

	}



	public function edit_special_pricing()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/discounts/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$data['side_menu'] = 'webshop';

		$data['current_tab'] = 'specialPricing';

		$data['PageTitle'] = 'Edit Special Pricing';



		$data['special_price_id'] = $special_price_id = $rule_Id = $this->uri->segment(3);

		$data['products_special_price'] = $this->WebshopModel->getSingleproducts_special_price($special_price_id);

		foreach ($data['products_special_price'] as $value) {

			$variants = $this->WebshopModel->getVariants($value->product_id);

			$variant = (object)$variants;



			$cat_name = $this->SellerProductModel->getProductsMaintCategoryNames($value->product_id);



			$value->cat_name = $cat_name;

			$value->variant = $variant;

			$ProductData[] = $value;

		}

		$data['product_details'] = $ProductData;

		$data['special_pricing_link'] = base_url('webshop/special-pricing');

		$this->load->view('webshop/discount/edit_special_pricing', $data);

	}



	public function get_webshop_price()

	{

		if (isset($_POST['product_id']) &&  $_POST['product_id'] != '') {

			$product_id = $_POST['product_id'];

			$webshop_price = $this->WebshopModel->getSingleDataByID('products', array('id' => $product_id), 'webshop_price');

			echo json_encode(array('flag' => 1, 'msg' => "nothing to update!", 'webshop_price' => $webshop_price));

			exit;

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "nothing to update!", 'webshop_price' => ""));

			exit;

		}

	}



	public function save_special_pricing()

	{

		if (!isset($_POST['product_id']) && $_POST['product_id'] == '' && !isset($_POST['webshop_price']) && $_POST['webshop_price']) {

			echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

			exit;

		} else {

			$product_id = $_POST['product_id'];



			$check_specidal_price_id = $this->WebshopModel->getSingleDataByID('products_special_prices', array('product_id' => $product_id), '*');

			if (empty($check_specidal_price_id)) {

				$display_original = isset($_POST['display_original']) ? 1 : 2;

				$loginId = $this->session->userdata('LoginID');

				$insertdata = array(

					'product_id' => $product_id,

					'special_price' => $_POST['special_price'],

					'special_price_from' => (isset($_POST['from_date']) && $_POST['from_date'] != '' ? strtotime($_POST['from_date']) : NULL),

					'special_price_to' => (isset($_POST['to_date']) && $_POST['to_date'] != '' ? strtotime($_POST['to_date']) : NULL),

					'display_original' => $display_original,

					'created_by' => $loginId,

					'created_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR']

				);

				$special_pricing_id = $this->WebshopModel->insertData('products_special_prices', $insertdata);



				if ($special_pricing_id) {

					$redirect = base_url('webshop/special-pricing');

					echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

					exit();

				} else {

					echo json_encode(array('flag' => 0, 'msg' => "Nothing to Insert!"));

					exit;

				}

			} else {



				$edit_link = base_url('webshop/edit-special-pricing/' . $check_specidal_price_id->id);

				$message = "Special pricing rule already added for this product with customer type -

					Please <a href='" . $edit_link . "' target='_blank'>Click Here!</a> to view the pricing rule";

				echo json_encode(array('flag' => 0, 'msg' => $message));

				exit;

			}

		}

	}



	public function update_special_pricing()

	{

		$special_price_id = $_POST['special_price_id'];

		$product_id = $_POST['product_id'];

		$display_original = isset($_POST['display_original']) ? 1 : 2;

		$loginId = $this->session->userdata('LoginID');

		$updatetdata = array(

			'product_id' => $product_id,

			'special_price' => $_POST['special_price'],

			'special_price_from' => strtotime($_POST['from_date']),

			'special_price_to' => strtotime($_POST['to_date']),

			'display_original' => $display_original,

			'updated_at' => time(),

			'ip' => $_SERVER['REMOTE_ADDR']

		);

		$where_arr = array('id' => $special_price_id, 'product_id' => $product_id);

		$special_pricing_id = $this->WebshopModel->updateNewData('products_special_prices', $where_arr, $updatetdata);



		if ($special_pricing_id) {

			$redirect = base_url('webshop/special-pricing');

			echo json_encode(array('flag' => 1, 'msg' => "Updated Successfully", 'redirect' => $redirect));

			exit();

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "Nothing to Update!"));

			exit;

		}

	}



	public function delete_special_pricing()

	{

		$row_id = $_POST['row_id'];



		$where_arr = array('id' => $row_id);

		$rowAffected = $this->WebshopModel->deleteData('products_special_prices', $where_arr);

		if ($rowAffected) {

			$redirect = base_url('webshop/special-pricing');

			echo json_encode(array('flag' => 1, 'msg' => "Successfully Deleted Row", 'redirect' => $redirect));

			exit();

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "nothing to delete!"));

			exit;

		}

	}



	public function newsLetter_subscriber()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/newsletter_subscriber',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/newsletter_subscriber/read', $this->session->userdata('userPermission'))) {

				// redirect('dashboard');

			}

		}

		$data['side_menu'] = 'webshop';

		$data['PageTitle'] = 'Newsletter Subscriber';

		$data['all_newsletter_subscriber'] = $this->WebshopModel->get_all_newsletter_subscriber();

		$this->load->view('webshop/newsletter_subscriber_list', $data);

	}



	public function edit_newsLetter_subscriber_text()

	{

		/* if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/newsletter_subscriber',$this->session->userdata('userPermission'))){

            redirect(base_url('dashboard'));  } */

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/newsletter_subscriber/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}

		$data['side_menu'] = 'webshop';

		$data['PageTitle'] = 'Edit Newsletter Subscriber Text';

		$data['news_letter_info'] = $this->CommonModel->get_webshop_texts();

		$this->load->view('webshop/edit_newsletter_subscriber_text', $data);

	}



	public function submit_newsletter_text()

	{

		if (isset($_POST['row_id']) && $_POST['row_id'] != '') {

			$update_data = array(

				'newsletter_title' => isset($_POST['title']) ? $_POST['title'] : '',

				'newsletter_message' => isset($_POST['news_letter_message']) ? $_POST['news_letter_message'] : '',



			);

			$where_arr = array('id' => $_POST['row_id']);

			$update_id = $this->WebshopModel->updateData('website_texts', $update_data, $where_arr);

			if ($update_id) {

				$redirect = base_url('webshop/edit-newsletter-subscriber-text');

				echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

				exit();

			} else {

				echo json_encode(array('flag' => 0, 'msg' => "Something went wrong!"));

				exit;

			}

		} else {



			$insertdata = array(

				'newsletter_title' => isset($_POST['title']) ? $_POST['title'] : '',

				'newsletter_message' => isset($_POST['news_letter_message']) ? $_POST['news_letter_message'] : '',

			);



			$insert_id = $this->WebshopModel->insertData('website_texts', $insertdata);

			if ($insert_id) {

				$redirect = base_url('webshop/edit-newsletter-subscriber-text');

				echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

				exit();

			} else {

				echo json_encode(array('flag' => 0, 'msg' => "Something went wrong!"));

				exit;

			}

		}

	}



	// stripe payment



	function stripe_account_link()

	{

		if (isset($_POST)) {



			$cUrl = $_POST['cUrl'];

			$account_id = $_POST['account_id'];

			$stripeAccountType = $_POST['stripeAccountType'];

			$payment_type_id = $_POST['payment_type_id'];

			/*$stripeEmail=$_POST['stripeEmail'];

			$stripeCountry=$_POST['stripeCountry'];*/

		}

		$refresh_url = $cUrl . $payment_type_id . '/reauth/';

		$return_url = $cUrl . $payment_type_id . '/return/';



		//exit();

		//$account_id='acct_1JwQi2SGZqqMd8EW';

        $stripeKeyId = STRIPE_SECRET_KEY;

		$apiTocken = "Authorization: Bearer " . $stripeKeyId;

		$url = 'https://api.stripe.com/v1/account_links';



		$dataJson = 'type=account_onboarding&refresh_url=' . $refresh_url . '&return_url=' . $refresh_url . '&account=' . $account_id;



		$curl = curl_init();



		curl_setopt_array($curl, array(

			CURLOPT_URL => $url,

			CURLOPT_RETURNTRANSFER => true,

			CURLOPT_SSL_VERIFYPEER => true,

			CURLOPT_ENCODING => "",

			CURLOPT_MAXREDIRS => 10,

			CURLOPT_TIMEOUT => 30,

			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

			CURLOPT_CUSTOMREQUEST => "POST",

			CURLOPT_POSTFIELDS => $dataJson,



			CURLOPT_HTTPHEADER => array(

				$apiTocken,

				"cache-control: no-cache"

			),

		));



		$response = curl_exec($curl);

		$err = curl_error($curl);



		curl_close($curl);



		if ($err) {

			print_r($err);

		} else {

			// print_r($response);

			$responseData = json_decode($response, true);

			// $responseData['url']

			$ResUrl = $responseData['url'];

			$update_data = array(

				'account_link_response' => $response

			);

			$where_arr = array('payment_id ' => $_POST['payment_type_id']);

			$update_id = $this->WebshopModel->updateData('webshop_payments_stripe', $update_data, $where_arr);



			echo json_encode(array('flag' => 1, 'msg' => "Success", 'url' => $ResUrl));

			exit();

			//exit();



		}

	}

	// end stripe payment





	/*start stripe Account create*/

	function stripe_create_account()

	{

		//print_r($_POST);

		if (isset($_POST)) {

			$payment_type_id = $_POST['payment_type_id'];

			$stripeAccountType = $_POST['stripeAccountType'];

			$stripeEmail = $_POST['stripeEmail'];

			$stripeCountry = $_POST['stripeCountry'];

		}



		$stripeData = $this->CommonModel->getSingleDataByID('payment_master', array('id' => 6), 'gateway_details');

		if (isset($stripeData) && $stripeData->gateway_details) {

			$masterStripeData = json_decode($stripeData->gateway_details);

			$stripeKeyId = $masterStripeData->key;

		} else {

			$stripeKeyId = '';

		}



		/*key stripe*/

		

		$url = 'https://api.stripe.com/v1/accounts';

		/*end key stripe*/

		$dataJson = 'type=' . $stripeAccountType . '&country=' . $stripeCountry . '&email=' . $stripeEmail;



		$curl = curl_init();



		curl_setopt_array($curl, array(

			CURLOPT_URL => $url,

			CURLOPT_RETURNTRANSFER => true,

			CURLOPT_SSL_VERIFYPEER => true,

			CURLOPT_ENCODING => "",

			CURLOPT_MAXREDIRS => 10,

			CURLOPT_TIMEOUT => 30,

			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

			CURLOPT_CUSTOMREQUEST => "POST",

			CURLOPT_POSTFIELDS => $dataJson,

			CURLOPT_HTTPHEADER => array(

				$apiTocken,

				"cache-control: no-cache"

			),

		));



		$response = curl_exec($curl);

		$err = curl_error($curl);



		curl_close($curl);



		if ($err) {



			print_r($err);

		} else {

			$responseData = json_decode($response, true);

			$connect_account_id = $responseData['id'];

			$insertdata = array(

				'payment_id' => $payment_type_id,

				//'ws_payment_id'=>$ws_payment_id,

				'create_con_act_response' => $response,

				'connect_account_id' => $connect_account_id,

				//'account_link_response'=>$account_link_response,

				//'is_able_to_enable'=>$is_able_to_enable,

				'status' => 1,

				'created_by' => $_SESSION['LoginID'],

				'created_at' => time(),

				'ip' => $_SERVER['REMOTE_ADDR']

			);

			$this->WebshopModel->insertData('webshop_payments_stripe', $insertdata);

			// echo json$response);

			//print_r($response->id);

			echo 1;

		}



		exit();

	}

	/*end stripe Account create*/



	/* stripe account retrive */

	function stripe_account_retrive($account_id)

	{

		$account_id = $account_id;



$stripeKeyId =  STRIPE_SECRET_KEY;

		

		$apiTocken = "Authorization: Bearer " . $stripeKeyId;

		$url = 'https://api.stripe.com/v1/accounts/' . $account_id;



		$curl = curl_init();



		curl_setopt_array($curl, array(

			CURLOPT_URL => $url,

			CURLOPT_RETURNTRANSFER => true,

			CURLOPT_SSL_VERIFYPEER => true,

			CURLOPT_ENCODING => "",

			CURLOPT_MAXREDIRS => 10,

			CURLOPT_TIMEOUT => 30,

			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

			CURLOPT_CUSTOMREQUEST => "GET",

			// CURLOPT_POSTFIELDS => $dataJson,



			CURLOPT_HTTPHEADER => array(

				$apiTocken,

				"cache-control: no-cache"

			),

		));



		$response = curl_exec($curl);

		$err = curl_error($curl);



		curl_close($curl);



		if ($err) {

			print_r($err);

		} else {

			//print_r($response);

			$responseData = json_decode($response, true);

			//print_r($responseData).'<br/><br/>';

			if (isset($responseData) && isset($responseData['details_submitted']) && $responseData['details_submitted'] == 1) { //active 1 true

				$account_details_submitted_status = 1;

			} else {

				$account_details_submitted_status = 0;

			}

			return $account_details_submitted_status;

		}

	}

	/* end stripe account retrive */



	/* stripe account updated */



	public function get_gatewayDetailsStripe()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/website_configuration', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$id = $this->uri->segment(3);

		$param = $this->uri->segment(4);

		if ($id == 6) {

			if ($param == 'reauth') {

				$update_data_res = array(

					'account_return_status' => 3

				);

				$where_arr_res = array('payment_id ' => $id);

				$this->WebshopModel->updateData('webshop_payments_stripe', $update_data_res, $where_arr_res);

			} elseif ($param == 'return') {

				$update_data_res = array(

					'account_return_status' => 2

				);

				$where_arr_res = array('payment_id ' => $id);

				$this->WebshopModel->updateData('webshop_payments_stripe', $update_data_res, $where_arr_res);

			}



			$data['side_menu'] = 'webshopPayment';

			$data['PageTitle'] = 'Payement Details';

			$data['owner_detail'] = $this->WebshopModel->getOwener($_SESSION['ShopOwnerId']);

			$data['shopData'] = $this->WebshopModel->getShopData($_SESSION['ShopOwnerId'], $_SESSION['ShopID']);

			$data['owner_country'] = $this->CommonModel->get_country_name_by_code($data['shopData']->country_code);



			$data['owner_email'] = $this->CommonModel->getSingleDataByID('fbc_users_webshop_details', array('shop_id' => $this->session->userdata('ShopID')), 'site_contact_email'); // new added

			$data['get_gateway_details'] = $this->WebshopModel->get_gateway_details($id);

			$data['shop_gateway_credentials'] = $this->WebshopModel->shop_gateway_credentials($id);

			$data['gateway_get_id'] = $id; //new added



			$data['webshopPaymentsStripe'] = $webshopPaymentsStripe = $this->CommonModel->getSingleShopDataByID('webshop_payments_stripe', array('payment_id' => $id), '*');

			if (isset($data['webshopPaymentsStripe'])) {

				//print_r($data['webshopPaymentsStripe']);

				if (isset($webshopPaymentsStripe->connect_account_id)) {

					$data['connect_account_id'] = $webshopPaymentsStripe->connect_account_id;

					$data['account_status'] = $this->stripe_account_retrive($webshopPaymentsStripe->connect_account_id);

					if (isset($data['account_status']) && $data['account_status'] == 1) {

						$update_data = array(

							'is_able_to_enable' => 1,

							'account_return_status' => 1

						);

						$where_arr = array('payment_id ' => $id);

						$update_id = $this->WebshopModel->updateData('webshop_payments_stripe', $update_data, $where_arr);

					}

				} else {

					$data['connect_account_id'] = '';

					$data['account_status'] = '';

				}

			}







			/*if($param=='reauth'){

				$update_data=array(

					'is_able_to_enable' =>1,

					'account_return_status' =>1

				);

				$where_arr=array('payment_id '=>$id);

				$update_id=$this->WebshopModel->updateData('webshop_payments_stripe',$update_data,$where_arr);

			}elseif($param=='return') {



			}*/



			/*if(isset($id))

			{*/



			//





			$this->load->view('webshop/webshop_payment_details.php', $data);

			// }

		}

	}



	/* end stripe account updated */



	//function stripe_create_webhook(){

	function stripe_create_webhook($stripeKeyId, $payment_id, $account_id)

	{

		$urlWebhook = base_url() . 'CheckoutController/checkoutSessionCompleted';

		$events = "checkout.session.completed";

		$apiTocken = "Authorization: Bearer " . $stripeKeyId;

		$url = 'https://api.stripe.com/v1/webhook_endpoints';

		/*end key stripe*/

		$dataJson = 'url=' . $urlWebhook . '&connect=true&enabled_events[]=' . $events;

		if (!empty($stripeKeyId) && $stripeKeyId != '') {

			$curl = curl_init();



			curl_setopt_array($curl, array(

				CURLOPT_URL => $url,

				CURLOPT_RETURNTRANSFER => true,

				CURLOPT_SSL_VERIFYPEER => true,

				CURLOPT_ENCODING => "",

				CURLOPT_MAXREDIRS => 10,

				CURLOPT_TIMEOUT => 30,

				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

				CURLOPT_CUSTOMREQUEST => "POST",

				CURLOPT_POSTFIELDS => $dataJson,

				CURLOPT_HTTPHEADER => array(

					$apiTocken,

					"cache-control: no-cache"

				),

			));



			$response = curl_exec($curl);

			$err = curl_error($curl);



			curl_close($curl);



			if ($err) {

				print_r($err);

			} else {

				$responseData = json_decode($response, true);



				if (isset($responseData) && !empty($responseData)) {

					if (isset($responseData['error'])) {



						echo json_encode(array('flag' => 0, 'msg' => $responseData['error']['message']));

						exit;

					} else {

						// exit();

						$webhook_status = $responseData['status'];

						$webhook_secret = $responseData['secret'];

						$webhook_url = $responseData['url'];



						$gateway_cred['connected_stripe_account_id'] = $account_id;

						$gateway_cred['checkout_session_completed_webhook_key'] = $webhook_secret;

						$gateway_cred['key'] = $stripeKeyId;

						$gateway_details =  json_encode($gateway_cred);

						if (empty($gateway_cred)) {

							$gateway_details = "";

						} else {

							// $gateway_details = "else";

						}

						$time = time();

						if ($webhook_status == 'enabled') {

							$status = 2;

						} else {

							$status = 1;

						}

						$update_array = array(

							"status" => 1,

							"gateway_details" => $gateway_details,

							"updated_at" => $time

						);

						$update_shop_gateway = $this->WebshopModel->update_shop_gateway($update_array, $payment_id);



						// webshop stripe payment

						$update_data = array(

							'status' => $status,

							'webhook_create_response' => $response

						);

						$where_arr = array('payment_id ' => $payment_id);

						$this->WebshopModel->updateData('webshop_payments_stripe', $update_data, $where_arr);

						echo json_encode(array('flag' => 1, 'msg' => "Success"));

						exit();

					}

				}

			}



			exit();

		} else {

		}

	}





	function stripe_account_Secret()

	{

		// print_r($_POST);

		$secretKey = $_POST['secretKey'];

		$payment_type_id = $_POST['payment_type_id'];

		$stripeAccountType = $_POST['stripeAccountType'];

		$account_id = $_POST['account_id'];

		// $response=$this->stripe_create_webhook($stripeKeyId);

		$response = $this->stripe_create_webhook($secretKey, $payment_type_id, $account_id);

		return $response;

	}

}

