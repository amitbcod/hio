<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ContactusController extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function contactus()
	{
		$data['PageTitle'] = 'Contact Us';

		$lang_code = '';
		if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
			$lang_code = $this->session->userdata('lcode');
		}
		$data['website_texts'] = $website_texts = HomeDetailsRepository::get_website_texts();
		$data['sess_lcode'] = $lang_code;

		$data['languageData'] = $this->get_communication_languages();

		$data['customer_name'] = isset($_SESSION) && isset($_SESSION['LoginID']) ? $_SESSION['FirstName'] . ' ' . $_SESSION['LastName'] : '';
		$data['customer_email'] = $_SESSION['EmailID'] ?? '';

		$data['contact_message'] = '';
		$data['contact_message2'] = '';
		$data['contact_message3'] = '';

		if (!empty($website_texts) && $website_texts->statusCode == 200) {
			if (!empty($website_texts->FbcWebsiteTexts->other_lang_message)) {
				$data['contact_message'] = $website_texts->FbcWebsiteTexts->other_lang_message;
			} elseif (!empty($website_texts->FbcWebsiteTexts->contact_message)) {
				$data['contact_message'] = $website_texts->FbcWebsiteTexts->contact_message;
			}

			if (!empty($website_texts->FbcWebsiteTexts->other_lang_message2)) {
				$data['contact_message2'] = $website_texts->FbcWebsiteTexts->other_lang_message2;
			} elseif (!empty($website_texts->FbcWebsiteTexts->contact_message2)) {
				$data['contact_message2'] = $website_texts->FbcWebsiteTexts->contact_message2;
			}

			if (!empty($website_texts->FbcWebsiteTexts->other_lang_message3)) {
				$data['contact_message3'] = $website_texts->FbcWebsiteTexts->other_lang_message3;
			} elseif (!empty($website_texts->FbcWebsiteTexts->contact_message3)) {
				$data['contact_message3'] = $website_texts->FbcWebsiteTexts->contact_message3;
			}
		}



		$this->template->load('common/contactus', $data);
	}

	public function contactus_post()
	{
		if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['comments'])) {
			echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
			exit;
		}

		if (!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $_POST["email"])) {
			echo json_encode(array('flag' => 0, 'msg' => "Please enter a valid Email address."));
			exit;
		}

		if (isset($_POST['nickname']) && $_POST['nickname'] != '') {
			echo json_encode(array(
				'flag' => 0,
				'msg' => "Are you a bot ?"
			));
			exit;
		}
		$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . RECAPTCHA_SECRETE_KEY_V3 . '&response=' . $_POST['g-recaptcha-response']);

		// Decode json data 
		$responseData = json_decode($verifyResponse);

		if (!$responseData->success) {
			echo json_encode(array('flag' => 0, 'msg' => "Please check on the reCAPTCHA box."));
			exit;
		}
		$custid = "";
		if (isset($_SESSION) && isset($_SESSION['LoginID'])) {
			$custid = $_SESSION['LoginID'];
		}

		$name = strip_tags($_POST['name']);
		$email = $_POST['email'];
		$comments = strip_tags($_POST['comments']);
		$order_flag = isset($_POST['order_flag']) ? $_POST['order_flag'] : '';
		$order_increment_id = isset($_POST['order_increment_id']) ? strip_tags($_POST['order_increment_id']) : '';
		$post_lcode = isset($_POST['lang_code']) ? $_POST['lang_code'] : '';

		// $shopcode = SHOPCODE;
		// $shop_id = SHOP_ID;
		// $data['webshop_details'] = CommonRepository::get_webshop_details($shopcode, $shop_id);
		// if (isset($data['webshop_details']) && $data['webshop_details']->is_success=='true') {
		// 	$shop_logo = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_logo);
		// }

		//	$webshopname = GlobalRepository::get_fbc_users_shop()?->result?->org_shop_name ?? '';

		$shop_logo = SITE_LOGO;
		$data['webshop_details'] = CommonRepository::get_webshop_details();
		if (!empty($data['webshop_details']) && isset($data['webshop_details']) && $data['webshop_details']->is_success == 'true') {
			$webshopname = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_name);
		}

		$site_logo =  '<a href="' . base_url() . '" style="color:#1E7EC8;">
				<img alt="' . $webshopname . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
			</a>';
		// $site_logo = '';
		$lang_code = '';
		if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
			$lang_code = $this->session->userdata('lcode');
		}

		$postArr = array(
			'name' => $name,
			'email' => $email,
			'order_flag' => $order_flag,
			'order_increment_id' => $order_increment_id,
			'content' => $comments,
			'customer_id' => $custid,
			'site_logo' => $site_logo,
			'lang_code' => $lang_code,
			'post_lcode' => $post_lcode
		);

		$ContactusResponse = ContactusRepository::contact_us($postArr);

		if (!empty($ContactusResponse) && $ContactusResponse->is_success == 'true') {
			$message = $ContactusResponse->message;
			$redirect = BASE_URL . "contact-us";
			echo json_encode(array('flag' => 1, 'msg' => $message, 'redirect' => $redirect));
			exit;
		}

		echo json_encode(array('flag' => 0, 'msg' => 'Email Not Sent'));
		exit;
	}

	private function get_communication_languages()
	{
		$multi_languages = $this->session->userdata('multi_lang_flag');

		if (empty($multi_languages) || $multi_languages != 1) {
			return '';
		}

		$languageData = '';
		$getLanguage = ContactusRepository::get_communication_lang_select(SHOPCODE, SHOP_ID);
		if (isset($getLanguage) && $getLanguage->statusCode == '200') {
			$languageData = $getLanguage->message;

			usort($languageData, function ($languageA, $languageB) {
				if ($languageA->is_default_language) {
					return -1;
				}
				if ($languageB->is_default_language) {
					return 1;
				}
				return 0;
			});
		}

		return $languageData;
	}
}
