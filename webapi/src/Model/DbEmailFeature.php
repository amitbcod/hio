<?php

use PHPMailer\PHPMailer\PHPMailer;

class DbEmailFeature
{
	private $dbl;

	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}

	public function getMultiLanguage($shopcode, $shop_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$param = array(1, 0);
		$query = "SELECT $shop_db.multi_languages.* FROM $shop_db.multi_languages WHERE $shop_db.multi_languages.is_communication_language = ? AND $shop_db.multi_languages.remove_flag = ? ORDER BY $shop_db.multi_languages.is_default_language DESC";

		$get_lang = $this->dbl->dbl_conn->rawQuery($query, $param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $get_lang;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getCodeByLanguageName($post_lcode)
	{
		// $shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		// $main_db = DB_NAME; //Constant variable

		$param = array($post_lcode, 0);
		$query = "SELECT multi_languages.* FROM multi_languages WHERE multi_languages.code = ? AND multi_languages.remove_flag = ?";
		$getCodeName = $this->dbl->dbl_conn->rawQueryOne($query, $param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $getCodeName;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	public function save_contact_us($email, $name, $mobileno = 'NULL', $cust_id = 'NULL', $message = 'NULL', $order_flag = 'NULL', $order_increment_id = 'NULL', $post_lcode = 'NULL')
	{
		// $shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable


		$cust_id = empty($cust_id) ? '0' : $cust_id;
		$time = time();
		$ip_address = $_SERVER['REMOTE_ADDR'];
		$order_flag = (empty($order_flag)) ? 0 : $order_flag;
		$order_increment_id = (empty($order_increment_id)) ? 'null' : $order_increment_id;
		$mobileno = (empty($mobileno)) ? 'null' : $mobileno;
		$post_lcode = (empty($post_lcode)) ? 'null' : $post_lcode;
		// echo "insert into contact_us(`name`,`customer_id`,`email`,`order_flag`,`order_increment_id`,`message`,`mobile_no`,`communication_language`,`created_at`,`ip`) VALUES ($name,$cust_id , $email,$order_flag,$order_increment_id, $message,$mobileno,$post_lcode,$time,$ip_address)";
		// die();
		$this->dbl->dbl_conn->insert('contact_us', [
			'name' => $name,
			'customer_id' => empty($cust_id) ? '0' : $cust_id,
			'email' => $email,
			'order_flag' => $order_flag,
			'order_increment_id' => $order_increment_id,
			'message' => $message,
			'mobile_no' => $mobileno,
			'communication_language' => $post_lcode,
			'created_at' => time(),
			'ip' => $_SERVER['REMOTE_ADDR'],
		]);
		// print_r($shopcode, $email, $name, $mobileno = false, $cust_id = false, $message = false, $order_flag = '', $order_increment_id = '', $post_lcode = '');
		// exit;

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			return true;
		} else {
			return false;
		}
	}


	public function sendCommonHTMLEmail($EmailTo, $identifier, $TempVars, $DynamicVars, $subject_param = '', $subject_param2 = '', $CommonVars = '', $lang_code = '')
	{
		$emailHeaderTemplate = $this->getWebShopEmailTemplateByCode('email-header', $lang_code);
		$emailFooterTemplate = $this->getWebShopEmailTemplateByCode('email-footer', $lang_code);
		$emailTemplate = $this->getWebShopEmailTemplateByCode($identifier, $lang_code);


		if (isset($emailHeaderTemplate['other_lang_content']) && $emailHeaderTemplate['other_lang_content'] != '') {
			$HeaderPart = $emailHeaderTemplate['other_lang_content'];
		} else {
			$HeaderPart = $emailHeaderTemplate['content'];
		}

		if (isset($emailFooterTemplate['other_lang_content']) && $emailFooterTemplate['other_lang_content'] != '') {
			$FooterPart = $emailFooterTemplate['other_lang_content'];
		} else {
			$FooterPart = $emailFooterTemplate['content'];
		}



		if (isset($CommonVars) && $CommonVars != '') {
			$HeaderPart = str_replace('##SITELOGO##', $CommonVars[0], $HeaderPart);
		}


		if (isset($CommonVars) && $CommonVars != '') {
			$HeaderPart = str_replace('##SITELOGO##', $CommonVars[0], $HeaderPart);
			$FooterPart = str_replace('##WEBSHOPNAME##', $CommonVars[1], $FooterPart);
		}


		if ($emailTemplate == false) {
			$subject = '';
			$emailBody = '';
		} else {

			if (isset($emailTemplate['other_lang_subject']) && $emailTemplate['other_lang_subject'] != '') {
				$subject = $emailTemplate['other_lang_subject'];
			} else {
				$subject = $emailTemplate['subject'];
			}

			if (isset($emailTemplate['other_lang_content']) && $emailTemplate['other_lang_content'] != '') {
				$emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate['other_lang_content']);
			} else {
				$emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate['content']);
			}
		}


		if (isset($subject_param) && $subject_param != '') {

			if ($identifier == 'new-order-confirmation') {
				$subject = str_replace('##INCREMENT_ID##', $subject_param, $subject);
			} else if ($identifier == 'customer-register-successful' || $identifier == 'customer-reset_password') {
				$subject = str_replace('##WEBSHOPNAME##', $subject_param, $subject);
			} else if ($identifier == 'storecredit-voucher-cancelnorder') {
				$subject = str_replace('##ORDERID##', $subject_param, $subject);
			} else if ($identifier == 'new-dropship-order-confirmation') {
				$subject = str_replace('##B2BORDERID##', $subject_param, $subject);
				if (isset($subject_param2) && $subject_param2 != '') {
					$subject = str_replace('##WEBSHOPNAME##', $subject_param2, $subject);
				} else {
					$subject = str_replace('##WEBSHOPNAME##', '', $subject);
				}
			} else if ($identifier == 'contact_us') {
				$subject = str_replace('##ORDERID##', $subject_param, $subject);
			}
		}




		$FinalContentBody = utf8_decode($HeaderPart . $emailBody . $FooterPart);
		$FromEmail = $this->get_custom_variable('admin_email');


		if ($FromEmail == false) {
			$from_email = 'no-reply@indiamags.com';
		} else {
			$from_email = $FromEmail['value'];
		}

		$headers = "From: " . $from_email . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

		$send_mail = $this->sendMailSMTP($EmailTo, $subject, $FinalContentBody, $from_email);

		if ($send_mail) {
			return true;
		} else {
			return false;
		}
	}

	public function sendCommonHTMLEmailTest($EmailTo, $identifier, $TempVars, $DynamicVars, $subject_param = '', $subject_param2 = '', $CommonVars = '', $lang_code = '')
	{
		$emailHeaderTemplate = $this->getWebShopEmailTemplateByCode('email-header', $lang_code);
		$emailFooterTemplate = $this->getWebShopEmailTemplateByCode('email-footer', $lang_code);
		$emailTemplate = $this->getWebShopEmailTemplateByCode($identifier, $lang_code);


		if (isset($emailHeaderTemplate['other_lang_content']) && $emailHeaderTemplate['other_lang_content'] != '') {
			$HeaderPart = $emailHeaderTemplate['other_lang_content'];
		} else {
			$HeaderPart = $emailHeaderTemplate['content'];
		}

		if (isset($emailFooterTemplate['other_lang_content']) && $emailFooterTemplate['other_lang_content'] != '') {
			$FooterPart = $emailFooterTemplate['other_lang_content'];
		} else {
			$FooterPart = $emailFooterTemplate['content'];
		}



		if (isset($CommonVars) && $CommonVars != '') {
			$HeaderPart = str_replace('##SITELOGO##', $CommonVars[0], $HeaderPart);
		}


		if (isset($CommonVars) && $CommonVars != '') {
			$HeaderPart = str_replace('##SITELOGO##', $CommonVars[0], $HeaderPart);
			$FooterPart = str_replace('##WEBSHOPNAME##', $CommonVars[1], $FooterPart);
		}


		if ($emailTemplate == false) {
			$subject = '';
			$emailBody = '';
		} else {

			if (isset($emailTemplate['other_lang_subject']) && $emailTemplate['other_lang_subject'] != '') {
				$subject = $emailTemplate['other_lang_subject'];
			} else {
				$subject = $emailTemplate['subject'];
			}

			if (isset($emailTemplate['other_lang_content']) && $emailTemplate['other_lang_content'] != '') {
				$emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate['other_lang_content']);
			} else {
				$emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate['content']);
			}
		}


		if (isset($subject_param) && $subject_param != '') {

			if ($identifier == 'new-order-confirmation') {
				$subject = str_replace('##INCREMENT_ID##', $subject_param, $subject);
			} else if ($identifier == 'customer-register-successful' || $identifier == 'customer-reset_password') {
				$subject = str_replace('##WEBSHOPNAME##', $subject_param, $subject);
			} else if ($identifier == 'storecredit-voucher-cancelnorder') {
				$subject = str_replace('##ORDERID##', $subject_param, $subject);
			} else if ($identifier == 'new-dropship-order-confirmation') {
				$subject = str_replace('##B2BORDERID##', $subject_param, $subject);
				if (isset($subject_param2) && $subject_param2 != '') {
					$subject = str_replace('##WEBSHOPNAME##', $subject_param2, $subject);
				} else {
					$subject = str_replace('##WEBSHOPNAME##', '', $subject);
				}
			} else if ($identifier == 'contact_us') {
				$subject = str_replace('##ORDERID##', $subject_param, $subject);
				// $cc = 'saroj@bcod.co.in,sayalik@bcod.co.in';
				// print_r($cc);die;

			}
		}




		$FinalContentBody = utf8_decode($HeaderPart . $emailBody . $FooterPart);
		$FromEmail = $this->get_custom_variable('admin_email');


		if ($FromEmail == false) {
			$from_email = 'no-reply@indiamags.com';
		} else {
			$from_email = $FromEmail['value'];
		}

		$headers = "From: " . $from_email . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

		$send_mail = $this->sendMailSMTPTest($EmailTo, $subject, $FinalContentBody, $from_email);

		if ($send_mail) {
			return true;
		} else {
			return false;
		}
	}


	// public function sendCommonHTMLEmail_test($EmailTo, $identifier, $TempVars, $DynamicVars, $subject_param = '', $subject_param2 = '', $CommonVars = '', $lang_code = '')
	// {
	// 	$emailHeaderTemplate = $this->getWebShopEmailTemplateByCode('email-header', $lang_code);
	// 	$emailFooterTemplate = $this->getWebShopEmailTemplateByCode('email-footer', $lang_code);
	// 	$emailTemplate = $this->getWebShopEmailTemplateByCode($identifier, $lang_code);


	// 	if (isset($emailHeaderTemplate['other_lang_content']) && $emailHeaderTemplate['other_lang_content'] != '') {
	// 		$HeaderPart = $emailHeaderTemplate['other_lang_content'];
	// 	} else {
	// 		$HeaderPart = $emailHeaderTemplate['content'];
	// 	}

	// 	if (isset($emailFooterTemplate['other_lang_content']) && $emailFooterTemplate['other_lang_content'] != '') {
	// 		$FooterPart = $emailFooterTemplate['other_lang_content'];
	// 	} else {
	// 		$FooterPart = $emailFooterTemplate['content'];
	// 	}



	// 	if (isset($CommonVars) && $CommonVars != '') {
	// 		$HeaderPart = str_replace('##SITELOGO##', $CommonVars[0], $HeaderPart);
	// 	}


	// 	if (isset($CommonVars) && $CommonVars != '') {
	// 		$HeaderPart = str_replace('##SITELOGO##', $CommonVars[0], $HeaderPart);
	// 		$FooterPart = str_replace('##WEBSHOPNAME##', $CommonVars[1], $FooterPart);
	// 	}


	// 	if ($emailTemplate == false) {
	// 		$subject = '';
	// 		$emailBody = '';
	// 	} else {

	// 		if (isset($emailTemplate['other_lang_subject']) && $emailTemplate['other_lang_subject'] != '') {
	// 			$subject = $emailTemplate['other_lang_subject'];
	// 		} else {
	// 			$subject = $emailTemplate['subject'];
	// 		}

	// 		if (isset($emailTemplate['other_lang_content']) && $emailTemplate['other_lang_content'] != '') {
	// 			$emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate['other_lang_content']);
	// 		} else {
	// 			$emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate['content']);
	// 		}
	// 	}


	// 	if (isset($subject_param) && $subject_param != '') {

	// 		if ($identifier == 'new-order-confirmation') {
	// 			$subject = str_replace('##INCREMENT_ID##', $subject_param, $subject);
	// 		} else if ($identifier == 'customer-register-successful' || $identifier == 'customer-reset_password') {
	// 			$subject = str_replace('##WEBSHOPNAME##', $subject_param, $subject);
	// 		} else if ($identifier == 'storecredit-voucher-cancelnorder') {
	// 			$subject = str_replace('##ORDERID##', $subject_param, $subject);
	// 		} else if ($identifier == 'new-dropship-order-confirmation') {
	// 			$subject = str_replace('##B2BORDERID##', $subject_param, $subject);
	// 			if (isset($subject_param2) && $subject_param2 != '') {
	// 				$subject = str_replace('##WEBSHOPNAME##', $subject_param2, $subject);
	// 			} else {
	// 				$subject = str_replace('##WEBSHOPNAME##', '', $subject);
	// 			}
	// 		}
	// 	}




	// 	$FinalContentBody = utf8_decode($HeaderPart . $emailBody . $FooterPart);
	// 	$FromEmail = $this->get_custom_variable('admin_email');


	// 	if ($FromEmail == false) {
	// 		$from_email = 'no-reply@indiamags.com';
	// 	} else {
	// 		$from_email = $FromEmail['value'];
	// 	}

	// 	$headers = "From: " . $from_email . "\r\n";
	// 	$headers .= "MIME-Version: 1.0\r\n";
	// 	$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

	// 	$send_mail = $this->sendMailSMTP($EmailTo, $subject, $FinalContentBody, $from_email);

	// 	if ($send_mail) {
	// 		return true;
	// 	} else {
	// 		return false;
	// 	}
	// }

	public function get_custom_variable($identifier)
	{
		$get_cms_page =  "SELECT * FROM custom_variables where  `identifier` = '$identifier'";

		$query  = $this->dbl->dbl_conn->rawQueryOne($get_cms_page);
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

	public function getFbcUsersWebShopSiteName($shopcode)
	{
		$main_db = DB_NAME; //Constant variable

		$shopid = str_replace("shop", "", $shopcode);
		$param = array($shopid);
		$query = "SELECT site_name FROM $main_db.fbc_users_webshop_details WHERE shop_id = ?";

		$Row = $this->dbl->dbl_conn->rawQueryOne($query, $param);
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

	public function getWebShopEmailTemplateByCode($identifier, $lang_code = '')
	{

		if ($lang_code != '') {
			$params = array($lang_code, $identifier);
			$Row = $this->dbl->dbl_conn->rawQueryOne("SELECT email_template.*,multi_lang_email_template.subject as other_lang_subject,multi_lang_email_template.content as other_lang_content FROM email_template LEFT JOIN multi_lang_email_template on (email_template.id = multi_lang_email_template.email_temp_id and  multi_lang_email_template.lang_code = ? ) WHERE email_code = ?", $params);
		} else {
			$params = array($identifier);
			$Row = $this->dbl->dbl_conn->rawQueryOne("SELECT * FROM email_template WHERE email_code = ?", $params);
		}

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


	public  function sendMailSMTP($to, $subject, $message, $from_email, $attachment = "")
	{

		$webshop_smtp_host = $this->get_custom_variable('smtp_host');
		$webshop_smtp_port = $this->get_custom_variable('smtp_port');
		$webshop_smtp_username = $this->get_custom_variable('smtp_username');
		$webshop_smtp_password = $this->get_custom_variable('smtp_password');
		$smtp_secure = $this->get_custom_variable('smtp_secure');

		// $getWebShopSiteName = $this->getFbcUsersWebShopSiteName($shopcode);
		// if(isset($getWebShopSiteName['site_name']) && $getWebShopSiteName['site_name'] !=''){
		// 	$SiteTitle = $getWebShopSiteName['site_name'];
		// }else{
		// 	$SiteTitle = SITE_TITLE;
		// }

		$mail = new PHPMailer();

		if ($webshop_smtp_host['value'] != '' && $webshop_smtp_port['value'] != '' && $webshop_smtp_username['value'] != '' && $webshop_smtp_password['value'] != '' && $smtp_secure['value'] != '') {

			$mail->IsSMTP();
			$mail->Host = $webshop_smtp_host['value']; //Hostname of the mail server
			$mail->SMTPDebug = 0;
			$mail->Port = $webshop_smtp_port['value']; //Port of the SMTP like to be 25, 80, 465 or 587
			$mail->SMTPAuth = true; //Whether to use SMTP authentication
			$mail->SMTPSecure = $smtp_secure['value'];
			$mail->Username = $webshop_smtp_username['value']; //Username for SMTP authentication any valid email created in your domain
			$mail->Password = $webshop_smtp_password['value']; //Password for SMTP authentication
			$mail->SetFrom($from_email); //From address of the mail
			$mail->IsHTML(true);
			$mail->Subject = $subject; //Subject od your mail

			if (is_string($to)) {
				$mail->AddAddress($to); //To address who will receive this email
				$mail->MsgHTML($message); //Put your body of the message you can place html code here
				$send = $mail->Send(); //Send the mails
			}

			if ($send) {
				return true;
			} else {
				return false;
			}
		} else {

			$mail->IsSMTP();
			$mail->Host = SMTP_HOST; //Hostname of the mail server
			$mail->SMTPDebug = 0;
			$mail->Port = SMTP_PORT; //Port of the SMTP like to be 25, 80, 465 or 587
			$mail->SMTPAuth = true; //Whether to use SMTP authentication
			$mail->SMTPSecure = 'ssl';
			$mail->Username = SMTP_UNAME; //Username for SMTP authentication any valid email created in your domain
			$mail->Password = SMTP_PWORD; //Password for SMTP authentication
			$mail->SetFrom($from_email); //From address of the mail
			$mail->IsHTML(true);
			$mail->Subject = $subject; //Subject od your mail

			if (is_string($to)) {
				$mail->AddAddress($to); //To address who will receive this email
				$mail->MsgHTML($message); //Put your body of the message you can place html code here
				// $mail->AddAttachment($attachment);
				$send = $mail->Send(); //Send the mails
			}

			if ($send) {
				return true;
			} else {
				return false;
			}
		}
	}

	public  function sendMailSMTPTest($to, $subject, $message, $from_email, $attachment = "")
	{

		$webshop_smtp_host = $this->get_custom_variable('smtp_host');
		$webshop_smtp_port = $this->get_custom_variable('smtp_port');
		$webshop_smtp_username = $this->get_custom_variable('smtp_username');
		$webshop_smtp_password = $this->get_custom_variable('smtp_password');
		$smtp_secure = $this->get_custom_variable('smtp_secure');

		// $getWebShopSiteName = $this->getFbcUsersWebShopSiteName($shopcode);
		// if(isset($getWebShopSiteName['site_name']) && $getWebShopSiteName['site_name'] !=''){
		// 	$SiteTitle = $getWebShopSiteName['site_name'];
		// }else{
		// 	$SiteTitle = SITE_TITLE;
		// }

		$mail = new PHPMailer();

		if ($webshop_smtp_host['value'] != '' && $webshop_smtp_port['value'] != '' && $webshop_smtp_username['value'] != '' && $webshop_smtp_password['value'] != '' && $smtp_secure['value'] != '') {

			$mail->IsSMTP();
			$mail->Host = $webshop_smtp_host['value']; //Hostname of the mail server
			$mail->SMTPDebug = 0;
			$mail->Port = $webshop_smtp_port['value']; //Port of the SMTP like to be 25, 80, 465 or 587
			$mail->SMTPAuth = true; //Whether to use SMTP authentication
			$mail->SMTPSecure = $smtp_secure['value'];
			$mail->Username = $webshop_smtp_username['value']; //Username for SMTP authentication any valid email created in your domain
			$mail->Password = $webshop_smtp_password['value']; //Password for SMTP authentication
			$mail->SetFrom($from_email); //From address of the mail
			$mail->IsHTML(true);
			$mail->Subject = $subject; //Subject od your mail

			if (is_string($to)) {
				$mail->AddAddress($to); //To address who will receive this email
				$mail->MsgHTML($message); //Put your body of the message you can place html code here
				// $mail->AddCC('sayalik@bcod.co.in');
				// $cc = 'saroj@bcod.co.in,sayalik@bcod.co.in';
				$send = $mail->Send(); //Send the mails
			}

			if ($send) {
				return true;
			} else {
				return false;
			}
		} else {

			$mail->IsSMTP();
			$mail->Host = SMTP_HOST; //Hostname of the mail server
			$mail->SMTPDebug = 0;
			$mail->Port = SMTP_PORT; //Port of the SMTP like to be 25, 80, 465 or 587
			$mail->SMTPAuth = true; //Whether to use SMTP authentication
			$mail->SMTPSecure = 'ssl';
			$mail->Username = SMTP_UNAME; //Username for SMTP authentication any valid email created in your domain
			$mail->Password = SMTP_PWORD; //Password for SMTP authentication
			$mail->SetFrom($from_email); //From address of the mail
			$mail->IsHTML(true);
			$mail->Subject = $subject; //Subject od your mail

			if (is_string($to)) {
				$mail->AddAddress($to); //To address who will receive this email
				$mail->MsgHTML($message); //Put your body of the message you can place html code here
				// $mail->AddAttachment($attachment);
				// $mail->AddCC('heeral@whuso.in');
				// $mail->AddCC('ronika@indiamags.com');
				// $mail->AddCC('suzan@indiamags.com');
				// $mail->AddCC('anu@bcod.co.in');
				// $mail->AddCC('snehals@bcod.co.in');
				// $mail->AddCC('saroj@bcod.co.in');
				// $mail->AddCC('bhagyashree@bcod.co.in');
				$ccEmail = "heeral@whuso.in,ronika@indiamags.com,suzan@indiamags.com,anu@bcod.co.in";
				if ($ccEmail != null) {
					$emailCC = explode(',', $ccEmail);
					foreach ($emailCC as $CCemail) {
						$mail->AddCC($CCemail);
					} //working
				}


				$send = $mail->Send(); //Send the mails
			}

			if ($send) {
				return true;
			} else {
				return false;
			}
		}
	}

	public function get_email_code_status($templateId)
	{
		$get_email_status =  "SELECT status FROM email_template where `email_code` = '$templateId'";
		$query  = $this->dbl->dbl_conn->rawQueryOne($get_email_status);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $query['status'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}
