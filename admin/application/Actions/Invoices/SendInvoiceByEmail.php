<?php

namespace App\Actions\Invoices;

class SendInvoiceByEmail
{
	private $ci;

	public function __construct()
	{
		$this->ci = &get_instance();

	}

	public function __invoke($invoice_id, $pdf_filename = null)
	{
		$invoicedata = $this->ci->WebshopOrdersModel->get_invoicedata_by_id($invoice_id);
		if($pdf_filename === null){
			$pdf_filename = $invoicedata->invoice_file;
		}
		// invoice send date add
		if($invoicedata->customer_id >0 ){
			$this->ci->WebshopOrdersModel->updateData(
				'customers_invoice',
				['customer_id'=>$invoicedata->customer_id],
				['last_invoice_sent_date'=>$invoicedata->invoice_date]
			);
		}

		$shop_id = $this->ci->session->userdata('ShopID');
		$Iwebshop_details=$this->ci->CommonModel->get_webshop_details($shop_id);
		$Ishop_logo = isset($Iwebshop_details) ? $this->ci->encryption->decrypt($Iwebshop_details['site_logo']) : '';
		$Ishop_logo = get_s3_url($Ishop_logo, $shop_id);

		$Isite_logo =  '<a href="'.base_url().'" style="color:#1E7EC8;">
							<img alt="'. $this->ci->CommonModel->getShopOwnerData($shop_id)->org_shop_name .'" border="0" src="'.$Ishop_logo.'" style="max-width:200px" />
						</a>';

		$attachment = get_s3_url('invoices/' . $pdf_filename, $shop_id);

		$this->ci->WebshopOrdersModel->sendInvoiceHTMLEmail(
			$invoicedata->bill_customer_email,
			'system-invoice',
			["##OWNER##" ,"##INVOICENO##","##WEBSHOPNAME##"],
			[
				trim($invoicedata->customer_first_name . ' ' . $invoicedata->customer_last_name),
				$invoicedata->invoice_no,
				$this->ci->CommonModel->getShopOwnerData($shop_id)->org_shop_name
			],
			[
				$Isite_logo,
				$this->ci->CommonModel->getShopOwnerData($shop_id)->org_shop_name,
				$invoicedata->invoice_no
			],
			$attachment);
	}

}
