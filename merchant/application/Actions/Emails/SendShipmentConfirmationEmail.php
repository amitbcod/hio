<?php

namespace App\Actions\Emails;

class SendShipmentConfirmationEmail
{
	private $templateId = 'fbcuser-order-shipment-created';
	private $ci;

	public function __construct(){
		$this->ci =& get_instance();

		$this->ci->load->model('WebshopOrdersModel');
		$this->ci->load->model('WebshopModel');
		$this->ci->load->library('encryption');
	}

	public function execute($OrderData, $shop_id, $owner_email, string $username, $increment_id, $additional_message = '')
	{

		$shop_owner = $this->ci->CommonModel->getShopOwnerData($shop_id);
		$shop_name = $shop_owner->org_shop_name;
		$site_logo = $this->getSite_logo($shop_id, $shop_name);

		$TempVars = ["##OWNER##", "##ORDERID##", "##MESSAGE##", "##WEBSHOPNAME##"];
		$DynamicVars = [$username, $increment_id, $additional_message, $shop_name];
		$CommonVars = [$site_logo, $shop_name];

		$lang_code = $this->getLangCode($OrderData);
		if(isset($this->templateId)){
			$emailSendStatusFlag=$this->ci->CommonModel->sendEmailStatus($this->templateId,$shop_id);
			if($emailSendStatusFlag==1){
				$this->ci->WebshopOrdersModel->sendCommonHTMLEmail($owner_email, $this->templateId, $TempVars, $DynamicVars, $increment_id, $CommonVars, $lang_code);
			}
		}
	}

	private function getSite_logo($shop_id, $shop_name): string
	{
		$webshop_details = $this->ci->CommonModel->get_webshop_details($shop_id);
		if (isset($webshop_details)) {
			$shop_logo = $this->ci->encryption->decrypt($webshop_details['site_logo']);
		}
		$burl = base_url();
		$shop_logo = get_s3_url($shop_logo ?? '', $shop_id);

		return  '<a href="'.getWebsiteUrl($shop_id,$burl).'" style="color:#1E7EC8;">
						<img alt="'.$shop_name.'" border="0" src="'.$shop_logo.'" style="max-width:200px" />
					</a>';
	}

	private function getLangCode($OrderData)
	{
		if ($OrderData->main_parent_id > 0) {
			$ParentOrderData = $this->ci->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $OrderData->main_parent_id), '');
			$is_default_language = $ParentOrderData->is_default_language;
			$language_code = $ParentOrderData->language_code;
		} else {
			$is_default_language = $OrderData->is_default_language;
			$language_code = $OrderData->language_code;
		}
		if ($is_default_language != 1 && $language_code != '') {
			$lang_code = $language_code;
		} else {
			$lang_code = '';
		}
		return $lang_code;
	}

}
