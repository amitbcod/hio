<?php

namespace App\Actions\Emails;

class SendTrackingEmail
{
	private $templateId = 'fbcuser-order-tracking-details';
	private $ci;

	public function __construct(){
		$this->ci =& get_instance();

		$this->ci->load->model('WebshopOrdersModel');
		$this->ci->load->model('WebshopModel');
		$this->ci->load->library('encryption');
	}

	public function execute($shop_id, $order_id, $tracking_id)
	{
		
		$shop_owner = $this->ci->CommonModel->getShopOwnerData($shop_id);
		$shop_name = $shop_owner->org_shop_name;
		$site_logo = $this->getSite_logo($shop_id, $shop_name);

		$OrderData=$this->ci->WebshopOrdersModel->getSingleDataByID('sales_order',array('order_id'=>$order_id),'');
		$shipment_details = $this->ci->WebshopOrdersModel->getSingleDataByID('sales_order_shipment_details',array('order_id'=>$order_id,'id'=>$tracking_id),'');

		$to = $OrderData->customer_email;
		$shop_name=$shop_owner->org_shop_name;
		$username = $OrderData->customer_firstname.' '.$OrderData->customer_lastname;
		$increment_id = $OrderData->increment_id;

		$box_no = "Box ".$shipment_details->box_number;
		$box_weight = $shipment_details->weight;
		$tracking_no = $shipment_details->tracking_id;
		$tracking_url = $shipment_details->tracking_url;

		$TempVars = array("##OWNER##" ,"##ORDERID##", "##BOXNO##", "##BOXWEIGHT##",  "##TRACKINGNO##",  "##TRACKINGURL##",'##WEBSHOPNAME##');
		$DynamicVars   = array($username,$increment_id,$box_no,$box_weight,$tracking_no,$tracking_url,$shop_name);
		$CommonVars=array($site_logo, $shop_name);

		$lang_code = $this->getLangCode($OrderData);
		if(isset($this->templateId)){
			$emailSendStatusFlag=$this->ci->CommonModel->sendEmailStatus($this->templateId,$shop_id);
			if($emailSendStatusFlag==1){
				$this->ci->WebshopOrdersModel->sendCommonHTMLEmail($to, $this->templateId, $TempVars,$DynamicVars,$increment_id,$CommonVars,$lang_code);
			}
		}
		$odr_update = ['email_sent_flag'=>1,'updated_at'=>time()];
		$where_arr = ['order_id'=>$order_id,'id'=>$tracking_id];
		$this->ci->WebshopOrdersModel->updateData('sales_order_shipment_details',$where_arr,$odr_update);
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
			$ParentOrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $OrderData->main_parent_id), '');
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
