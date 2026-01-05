<?php
class CronTestModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		// $fbc_user_id	=	$this->session->userdata('ShopOwnerId');  //old LoginID
		// $shop_id		=	$this->session->userdata('ShopID');

		// $FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('fbc_user_id'=>$fbc_user_id),'shop_id,fbc_user_id,database_name');
		// if(isset($FBCData) && $FBCData->database_name!='')
		// {
		// 	$fbc_user_database=$FBCData->database_name;

		// 	$this->load->database();
		// 	$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
		// 	$this->seller_db = $this->load->database($config_app,TRUE);
		// 	if($this->seller_db->conn_id) {
		// 		//do something
		// 	} else {
		// 		redirect(base_url());
		// 	}
		// }else{
		// 	redirect(base_url());
		// }
	}



		// insert data
	  public function insertData($table,$data)
	  {
		 $this->seller_db->reset_query();

	    $this->seller_db->insert($table,$data);
	    if($this->seller_db->affected_rows() > 0)
	    {
			$last_insert_id=$this->seller_db->insert_id();
	      return $last_insert_id;
	    }
	    else
	    {
	      return false;
	    }
	  }

	public function getEmailTemplateByIdentifier($identifier){
		$result = $this->seller_db->get_where('email_template',array('email_code'=>$identifier))->row();
		return $result;
	}

	// invoice file name
	public function getInvoiceFileName($invoiceID){
		$result = $this->seller_db->get_where('invoicing',array('id'=>$invoiceID))->row();
		return $result;
	}


	// send invoice email
	public function sendInvoiceHTMLEmail($shopId,$EmailTo, $identifier, $TempVars, $DynamicVars,$CommonVars,$attachment){
		/*$GlobalVar=$this->getCustomVariableByIdentifier('admin_email');
		if(isset($GlobalVar) && $GlobalVar->value!=''){
			$from_email=$GlobalVar->value;
		}else{*/
			$shop_id		=	$shopId;
			// $shop_id		=	$this->session->userdata('ShopID');
			$FBCData=$this->CommonModel->getShopOwnerData($shop_id);
			$from_email=$FBCData->email;
		//}

		$emailTemplate = $this->getEmailTemplateByIdentifier($identifier);
		if(isset($emailTemplate) && $emailTemplate->id!='')
		{

			$emailHeaderTemplate = $this->getEmailTemplateByIdentifier('email-header');
			$emailFooterTemplate = $this->getEmailTemplateByIdentifier('email-footer');

			$HeaderPart=$emailHeaderTemplate->content;
			$FooterPart=$emailFooterTemplate->content;
			if(isset($CommonVars) && $CommonVars!='')
			{
				$HeaderPart = str_replace('##SITELOGO##', $CommonVars[0], $HeaderPart);
				$FooterPart = str_replace('##WEBSHOPNAME##', $CommonVars[1], $FooterPart);
			}

			$templateId=$emailTemplate->id;

			$subject = $emailTemplate->subject;
			$title = $emailTemplate->title;

			if($templateId==21){
				if(isset($CommonVars) && $CommonVars!=''){
					$subject = str_replace('##INVOICENO##', $CommonVars[2], $subject);
					$subject = str_replace('##WEBSHOPNAME##', $CommonVars[1], $subject);
				}else{
					$subject = str_replace('##INVOICENO##', '', $subject);
					$subject = str_replace('##WEBSHOPNAME##', '', $subject);
				}
			}

			$emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate->content);

			/*
			$data['title'] = $title;
			$data['subject'] = $subject;
			$data['content'] = $emailBody;

			$content = $this->load->view('email_template/email_content', $data, TRUE);
			*/

			$FinalContentBody=$HeaderPart.$emailBody.$FooterPart;

			if($this->CommonModel->sendHTMLMailSMTPAttchment($EmailTo, $subject, $FinalContentBody,$from_email, $attachment))
			{
				$this->email->clear(TRUE);
				return true;
			}else{

				return false;
			}

		}
	}

	// webshop
	public function sendInvoiceHTMLEmailWebshop($shopId,$EmailTo, $identifier, $TempVars, $DynamicVars,$CommonVars,$attachment){

		// get invoice file name
		/*if($attachment){
			$invoiceData=$this->getInvoiceFileName($attachment);
			//echo $invoiceData->invoice_file;
		}*/



		//print_r($attachment);
		/*$GlobalVar=$this->getCustomVariableByIdentifier('admin_email');
		if(isset($GlobalVar) && $GlobalVar->value!=''){
			$from_email=$GlobalVar->value;
		}else{*/
			$shop_id		=	$shopId;
			// $shop_id		=	$this->session->userdata('ShopID');
			$FBCData=$this->CommonModel->getShopOwnerData($shop_id);
			$from_email=$FBCData->email;
		//}

		$emailTemplate = $this->getEmailTemplateByIdentifier($identifier);
		if(isset($emailTemplate) && $emailTemplate->id!='')
		{

			$emailHeaderTemplate = $this->getEmailTemplateByIdentifier('email-header');
			$emailFooterTemplate = $this->getEmailTemplateByIdentifier('email-footer');

			$HeaderPart=$emailHeaderTemplate->content;
			$FooterPart=$emailFooterTemplate->content;
			if(isset($CommonVars) && $CommonVars!='')
			{
				$HeaderPart = str_replace('##SITELOGO##', $CommonVars[0], $HeaderPart);
				$FooterPart = str_replace('##WEBSHOPNAME##', $CommonVars[1], $FooterPart);
			}

			$templateId=$emailTemplate->id;

			$subject = $emailTemplate->subject;
			$title = $emailTemplate->title;

			if($templateId==21){
				if(isset($CommonVars) && $CommonVars!=''){
					$subject = str_replace('##INVOICENO##', $CommonVars[2], $subject);
					$subject = str_replace('##WEBSHOPNAME##', $CommonVars[1], $subject);
				}else{
					$subject = str_replace('##INVOICENO##', '', $subject);
					$subject = str_replace('##WEBSHOPNAME##', '', $subject);
				}
			}

			$emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate->content);

			/*
			$data['title'] = $title;
			$data['subject'] = $subject;
			$data['content'] = $emailBody;

			$content = $this->load->view('email_template/email_content', $data, TRUE);
			*/

			$FinalContentBody=$HeaderPart.$emailBody.$FooterPart;

			if($this->CommonModel->sendHTMLMailSMTPAttchment($EmailTo, $subject, $FinalContentBody,$from_email, $attachment))
			{
				$this->email->clear(TRUE);
				return true;
			}else{

				return false;
			}



		}
	}
	// end email


}
