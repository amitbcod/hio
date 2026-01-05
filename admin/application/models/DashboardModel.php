<?php
defined('BASEPATH') or exit('No direct script access allowed');

###+------------------------------------------------------------------------------------------------
###| BCOD WEB SOLUTIONS PVT. LTD., MUMBAI [ www.bcod.co.in ]
###+------------------------------------------------------------------------------------------------
###| Code By - Ketan Vyas (ketanv@bcod.co.in)
###+------------------------------------------------------------------------------------------------
###| Date - Dec 2022
###+------------------------------------------------------------------------------------------------
use App\Services\Trackers\ShipmentStatusEnum;

class DashboardModel extends CI_Model
{

	public function email_exists($email, $id = '')
	{
		$this->db->where('email', $email);
		if ($id != '') {
			$this->db->where('id !=', $id);
		}
		$query = $this->db->get('adminusers');
		if ($query->num_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function check_user_email_exists($curr_email, $id)
	{
		$this->db->where('email', $curr_email);
		$this->db->where('id', $id);
		$query = $this->db->get('adminusers');
		if ($query->num_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function check_user_exists($id)
	{
		$this->db->where('id', $id);
		$query = $this->db->get('adminusers');
		return $query->result_array();
	}

	public function update_admin_user_email($data)
	{
		$this->db->where($data['condition']);
		$query = $this->db->update('adminusers', $data['data']);
		if ($query) return true;
		else return false;
	}

	public function check_user_password_exists($curr_pass, $id)
	{
		$this->db->where('password', $curr_pass);
		$this->db->where('id', $id);
		$query = $this->db->get('adminusers');
		if ($query->num_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function update_admin_user_password($data)
	{
		$this->db->where($data['condition']);
		$query = $this->db->update('adminusers', $data['data']);
		if ($query) return true;
		else return false;
	}

	public function update_admin_user_details($conditions)
	{
		$this->db->where($conditions['condition']);
		$query = $this->db->update('adminusers', $conditions['data']);
		if ($query) return true;
		else return false;
	}
	public function get_customer_details($order_id)
	{
		$this->db->select('*');
		$this->db->from('sales_order');
		// $this->db->where('item_id', $item_id);
		$this->db->where('order_id', $order_id);

		$query = $this->db->get();
		// echo $this->db->last_query();die;

		return $query->row();
	}
	public function getOrderPaymentDataById($order_id)
	{
		$this->db->select('*');
		$this->db->from('sales_order_payment');
		$this->db->where('order_id', $order_id);

		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}
	public function abundantCartProductImagesDetails($parent_product_id, $product_id)
	{
		$this->db->select('*');
		$this->db->from('products');
		if ($parent_product_id != 0) {
			$this->db->where('id', $parent_product_id);
		} else {
			$this->db->where('id', $product_id);
		}
		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}
	public function getRenewalOrderItems($order_id)
	{
		$this->db->select('*');
		$this->db->from('sales_order_items');
		$this->db->where('order_id', $order_id);
		$this->db->where('product_type !=', 'bundle');


		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}
	public function getEmailTemplateById($TemplateId)
	{
		$result = $this->db->get_where('email_template', array('id' => $TemplateId))->row();
		return $result;
	}

	public function getEmailTemplateByIdentifier($identifier, $lang_code = '')
	{
		if ($lang_code != '') {
			$this->db->select('email_template.*,multi_lang_email_template.subject as other_lang_subject,multi_lang_email_template.content as other_lang_content');
			$this->db->from('email_template');
			$this->db->join("multi_lang_email_template", "email_template.id = multi_lang_email_template.email_temp_id and  multi_lang_email_template.lang_code ='$lang_code'", "LEFT");
			$this->db->where('email_template.email_code', $identifier);
			$query = $this->db->get();
			$result = $query->row();
		} else {
			$result = $this->db->get_where('email_template', array('email_code' => $identifier))->row();
		}
		return $result;
	}

	public function getCustomVariableByIdentifier($identifier)
	{
		$result = $this->db->get_where('custom_variables', array('identifier' => $identifier))->row();
		return $result;
	}

	public function sendCommonHTMLEmail($EmailTo, $identifier, $TempVars, $DynamicVars, $SubDynamic = '', $CommonVars = '', $lang_code = '')
	{

		$webshop_smtp_host = 'smtp.gmail.com'; // $this->getCustomVariableByIdentifier('smtp_host');
		$webshop_smtp_port =  465; // $this->getCustomVariableByIdentifier('smtp_port') ??
		$webshop_smtp_username = 'care@indiamags.com'; //$this->getCustomVariableByIdentifier('smtp_username') ??
		$webshop_smtp_password = 'ZwupmjHd@!1852'; //$this->getCustomVariableByIdentifier('smtp_password') ??
		$webshop_smtp_secure = $this->getCustomVariableByIdentifier('smtp_secure');

		$GlobalVar = $this->getCustomVariableByIdentifier('admin_email');
		if (isset($GlobalVar) && $GlobalVar->value != '') {
			$from_email = $GlobalVar->value;
		} else {
			$shop_id		=	$this->session->userdata('ShopID');
			$FBCData = $this->CommonModel->getShopOwnerData($shop_id);
			$from_email = $FBCData->email;
		}
		if ($lang_code != '') {
			$emailTemplate = $this->getEmailTemplateByIdentifier($identifier, $lang_code);
		} else {
			$emailTemplate = $this->getEmailTemplateByIdentifier($identifier);
		}

		if (isset($emailTemplate) && $emailTemplate->id != '') {

			if ($lang_code != '') {
				$emailHeaderTemplate = $this->getEmailTemplateByIdentifier('email-header', $lang_code);
				$emailFooterTemplate = $this->getEmailTemplateByIdentifier('email-footer', $lang_code);
			} else {
				$emailHeaderTemplate = $this->getEmailTemplateByIdentifier('email-header');
				$emailFooterTemplate = $this->getEmailTemplateByIdentifier('email-footer');
			}

			if (isset($emailTemplate->other_lang_content) && $emailTemplate->other_lang_content != '') {
				$emailContent = $emailTemplate->other_lang_content;
			} else {
				$emailContent = $emailTemplate->content;
			}
			if (isset($emailTemplate->other_lang_subject) && $emailTemplate->other_lang_subject != '') {
				$subject = $emailTemplate->other_lang_subject;
			} else {
				$subject = $emailTemplate->subject;
			}

			$HeaderPart = $emailHeaderTemplate->content;
			$FooterPart = $emailFooterTemplate->content;
			if (isset($CommonVars) && $CommonVars != '') {
				$HeaderPart = str_replace('##SITELOGO##', $CommonVars[0], $HeaderPart);
				$FooterPart = str_replace('##WEBSHOPNAME##', $CommonVars[1], $FooterPart);
			}

			$templateId = $emailTemplate->id;

			/*if(isset($SubDynamic) && $SubDynamic!=''){
				$subject = $SubDynamic;
			}
			else
			{
				$subject = $emailTemplate->subject;
			}*/

			//$subject = $emailTemplate->subject;


			$title = $emailTemplate->title;

			if ($templateId == 4 || $templateId == 6 || $templateId == 18 || $templateId == 19 || $templateId == 20) {
				if (isset($SubDynamic) && $SubDynamic != '') {
					$subject = str_replace('##ORDERID##', $SubDynamic, $subject);
				} else {
					$subject = str_replace('##ORDERID##', '', $subject);
				}
			} else if ($templateId == 15) {
				if (isset($SubDynamic) && $SubDynamic != '') {
					$subject = str_replace('##RETURNORDERID##', $SubDynamic, $subject);
				} else {
					$subject = str_replace('##RETURNORDERID##', '', $subject);
				}
			} else if ($templateId == 13 || $templateId == 12) {
				if (isset($SubDynamic) && $SubDynamic != '') {
					$subject = $SubDynamic;
				} else {
					$subject = $subject;
				}
			} else if ($identifier == 'fbcuser-order-cancelled-by-fbcuser') {
				if (isset($DynamicVars) && $DynamicVars != '') {
					$subject = str_replace('##ORDERID##', $DynamicVars[1], $subject);
				}
			} else if ($identifier == 'request_a_payment_for_order') {
				if (isset($SubDynamic) && $SubDynamic != '') {
					$subject = str_replace('##ORDERID##', $SubDynamic, $subject);
				} else {
					$subject = str_replace('##ORDERID##', '', $subject);
				}
			}

			if ($identifier == 'initiate_refund') {
				if (isset($SubDynamic) && $SubDynamic != '') {
					$subject = str_replace('##ORDERID##', $SubDynamic, $subject);
				} else {
					$subject = str_replace('##ORDERID##', '', $subject);
				}
			}



			$emailBody = str_replace($TempVars, $DynamicVars, $emailContent);


			if ($identifier == 'customer_subscription_renewal') {
				if (isset($identifier) && $identifier != '') {
					$subject = str_replace('##CUSTOMERORDERID##', $SubDynamic[0], $subject);
				}
				// $emailBody = str_replace('##CUSTOMERORDERID##', $DynamicVars[2], $emailTemplate->content);
				$emailBody = str_replace('##CUSTOMERORDERID##', $DynamicVars[0], $emailBody);
				// $emailBody = str_replace('##CUSTOMERMOBILENO##', $DynamicVars[1], $emailBody);
				// $from_email = 'ameyas@bcod.co.in';
				$cc = 'snehals@bcod.co.in';
				print_r($cc);
			}
			// die;
			$FinalContentBody = utf8_decode($HeaderPart . $emailBody . $FooterPart);
			// die;
			if ($this->CommonModel->sendHTMLMailSMTP($EmailTo, $subject, $FinalContentBody, $from_email, $attachment = "", $webshop_smtp_host, $webshop_smtp_port, $webshop_smtp_username, $webshop_smtp_password, $webshop_smtp_secure, $cc)) {
				return true;
			} else {

				return false;
			}
		} else {
		}
	}
	public function updateEmailSentFlag($order_id)
	{
		// print_r($order_id);die;

		$SentMailData = array(
			'renewal_email_sent'		=> 1,
			'renewal_email_sent_date' => strtotime(date('Y-m-d H:i:s')),
		);
		$this->db->where('order_id', $order_id);

		return $this->db->update('sales_order_items', $SentMailData);
		// echo $this->db->last_Query();die;
	}
	public function get_store_mobile_details()
	{
		$this->db->select('*');
		$this->db->from('custom_variables');
		$this->db->where('identifier', 'store_mobile');
		$query = $this->db->get();
		return $query->row();
	}
}
