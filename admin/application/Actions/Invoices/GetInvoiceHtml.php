<?php

namespace App\Actions\Invoices;

class GetInvoiceHtml
{
	private $ci;

	public function __construct()
	{
		$this->ci = &get_instance();

	}

	public function __invoke($invoice_id)
	{
		$data['invoicedata']=$this->ci->WebshopOrdersModel->get_invoicedata_by_id($invoice_id);

		// Shop Data
		$data['custom_variables']=$this->ci->CommonModel->get_custom_variables();
		//getSingleDataByID
		$data['shop_id'] = $this->ci->session->userdata('ShopID');
		$data['user_web_shop_details'] = $this->ci->CommonModel->get_webshop_details($data['shop_id']);
		$data['user_details'] = $this->ci->CommonModel->GetUserByUserId($_SESSION['LoginID']);
		if($data['user_details']->parent_id == 0)
		{
			$data['user_shop_details'] = $this->ci->UserModel->getShopDetailsByfbcuserid($data['user_details']->fbc_user_id);
		}else{
			$data['user_shop_details'] = $this->ci->UserModel->getShopDetailsByfbcuserid($data['user_details']->parent_id);
		}

		return $this->ci->load->view('invoice/webshop/invoice_format_shop1',$data,true);

	}
}
