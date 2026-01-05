<?php

namespace App\Actions\Invoices;

class GenerateInvoicePdf
{

	private $ci;

	public function __construct()
	{
		$this->ci = &get_instance();
	}

	public function __invoke($html, $invoiceID, $shop_id){
		$this->ci->load->library('Pdf_dom');
		$filename = $this->ci->pdf_dom->createbyshop($html,$invoiceID,$shop_id);

		$this->ci->B2BOrdersModel->updateData(
			'invoicing',
			['id'=>$invoiceID],
			['invoice_file'=>$filename]
		);

		return $filename;
	}
}
