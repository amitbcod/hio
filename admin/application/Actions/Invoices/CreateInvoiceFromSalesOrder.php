<?php

namespace App\Actions\Invoices;

class CreateInvoiceFromSalesOrder
{
	private $ci;

	public function __construct()
	{
		$this->ci = &get_instance();
		$this->ci->load->model('SalesOrderPaymentModel');
	}

	public function __invoke($order_id, $billDate)
	{
		$orderData = $this->ci->WebshopOrdersModel->get_webshop_invoicing_data($order_id);

		if(!empty($orderData->invoice_id) & $orderData->invoice_id > 0){
			return $orderData->invoice_id;
		}

		$orderUserType = $orderData->checkout_method;

		$BillingAddress = $this->ci->WebshopOrdersModel->getSingleDataByID('sales_order_address', ['order_id' => $orderData->order_id, 'address_type' => 1], '');
		$ShippingAddress = $this->ci->WebshopOrdersModel->getSingleDataByID('sales_order_address', ['order_id' => $orderData->order_id, 'address_type' => 2], '');

		$customVariables_invoice_prefix = $this->ci->CommonModel->getSingleShopDataByID('custom_variables', ['identifier' => 'invoice_prefix'], 'value');
		$customVariables_invoice_no = $this->ci->CommonModel->getSingleShopDataByID('custom_variables', ['identifier' => 'invoice_next_no'], 'value');

		$invoiceNo = $customVariables_invoice_no->value ?? 1;
		$invoice_next_no = $invoiceNo + 1;
		$invoice_no = ($customVariables_invoice_prefix->value ?? 'INV') . $invoiceNo;

		$invoice_date = !empty($billDate) ? date(strtotime($billDate)) : time();

		$customer_company = $BillingAddress->company_name;
		$customer_gst_no = $BillingAddress->vat_no;

		$bill_customer_email = $orderData->customer_email;
		$bill_customer_id = $orderData->customer_id;

		if ((int) $orderData->invoice_self === 1) {
			if ($orderUserType === 'guest') {
				$payment_term = 0;
				$invoice_due_date = $invoice_date;
			} else {
				$customers_invoiceData = $this->ci->CommonModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $orderData->customer_id), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
				$payment_term = $customers_invoiceData->payment_term ?? 0;

				if ($invoice_date && $payment_term > 0) {
					$dateAdd = date(DATE_PIC_FM, $invoice_date); // invoice due date
					$due_date = date('Y-m-d', strtotime($dateAdd . ' + ' . $payment_term . ' days'));
					$invoice_due_date = date(strtotime($due_date));
				} else {
					$payment_term = 0;
					$invoice_due_date = $invoice_date;
				}

				if(empty($customer_gst_no) && empty($customer_company)) {
					$bill_customer_company_name_gst = $this->ci->CommonModel->getSingleShopDataByID('customers', array('id' => $orderData->customer_id), 'company_name,gst_no,CONCAT(first_name, " ", last_name) as customer_name');
					if (isset($bill_customer_company_name_gst) && (!empty($bill_customer_company_name_gst->company_name) || !empty($bill_customer_company_name_gst->gst_no))) {
						$customer_company = $bill_customer_company_name_gst->company_name;
						$customer_gst_no = $bill_customer_company_name_gst->gst_no;
					}
				}
			}

		} elseif ($orderUserType === 'guest') {
			$payment_term = 0;
			$invoice_due_date = $invoice_date;
			$customVariables_alternative_email_id = $this->ci->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');
			if (isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value) {
				$customer_email_alternate_shop = $customVariables_alternative_email_id->value;
				$customer_email_data_shop = $this->ci->CommonModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate_shop), 'email_id');
				$bill_customer_email = $customer_email_data_shop->email_id;
				$bill_customer_id = $customer_email_alternate_shop;
			}
		} else {
			$customers_invoiceData = $this->ci->CommonModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $orderData->customer_id), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');

			if (isset($customers_invoiceData) && !empty($customers_invoiceData)) {
				$payment_term = $customers_invoiceData->payment_term;
				$customer_email_alternate = '';
				$invoice_to_type = $customers_invoiceData->invoice_to_type;
				if ($invoice_to_type != 0) {
					$customer_email_alternate = $customers_invoiceData->alternative_email_id;
				}
			} else {
				$payment_term = 0;

				$customVariables_alternative_email_id = $this->ci->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');
				if (isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value) {
					$customer_email_alternate_shop = $customVariables_alternative_email_id->value;
					$customer_email_data_shop = $this->ci->CommonModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate_shop), 'email_id');
					$bill_customer_email = $customer_email_data_shop->email_id;
					$bill_customer_id = $customer_email_alternate_shop;
				}
			}

			if ($invoice_date && $payment_term > 0) {
				$dateAdd = date(DATE_PIC_FM, $invoice_date); // invoice due date
				$due_date = date('Y-m-d', strtotime($dateAdd . ' + ' . $payment_term . ' days'));
				$invoice_due_date = date(strtotime($due_date));
			} else {
				$payment_term = 0;
				$invoice_due_date = $invoice_date;
			}

			if (isset($invoice_to_type) && $invoice_to_type == 1) {
				if (!empty($customer_email_alternate)) {
					$customer_email_data = $this->ci->CommonModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate), 'email_id');
					if (isset($customer_email_data) && !empty($customer_email_data)) {
						$bill_customer_email = $customer_email_data->email_id;
						$bill_customer_id = $customer_email_alternate;
						$customers_invoiceData_alt = $this->ci->CommonModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_email_alternate), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
						if (isset($customers_invoiceData_alt) && !empty($customers_invoiceData_alt)) {
							$payment_term = $customers_invoiceData_alt->payment_term;
						} else {
							$payment_term = 0;
						}

					} else {
						$customVariables_alternative_email_id = $this->ci->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');
						if (isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value) {
							$customer_email_alternate_shop = $customVariables_alternative_email_id->value;
							$customer_email_data_shop = $this->ci->CommonModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate_shop), 'email_id');
							$bill_customer_email = $customer_email_data_shop->email_id;
							$bill_customer_id = $customer_email_alternate_shop;
							$customers_invoiceData_alt_cusVar = $this->ci->CommonModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_email_alternate_shop), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
							if (isset($customers_invoiceData_alt_cusVar) && !empty($customers_invoiceData_alt_cusVar)) {
								$payment_term = $customers_invoiceData_alt_cusVar->payment_term;
							} else {
								$payment_term = 0;
							}
						}
					}
				} else {
					$customVariables_alternative_email_id = $this->ci->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');
					if (isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value) {
						$customer_email_alternate_shop = $customVariables_alternative_email_id->value;
						$customer_email_data_shop = $this->ci->CommonModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate_shop), 'email_id');
						$bill_customer_email = $customer_email_data_shop->email_id;
						$bill_customer_id = $customer_email_alternate_shop;

						$customers_invoiceData_alt_cusVar = $this->ci->CommonModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_email_alternate_shop), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
						if (isset($customers_invoiceData_alt_cusVar) && !empty($customers_invoiceData_alt_cusVar)) {
							$payment_term = $customers_invoiceData_alt_cusVar->payment_term;
						} else {
							$payment_term = 0;
						}
					}
				}

				if ($bill_customer_id) {
					$Default_BillingAddress = $this->ci->WebshopOrdersModel->getSingleDataByID('customers_address', array('customer_id' => $bill_customer_id, 'is_default' => 1), '');
					if (isset($Default_BillingAddress)) {
						$BillingAddress = $Default_BillingAddress;
					} else {
						$bill_BillingAddress = $this->ci->WebshopOrdersModel->getSingleDataByID('customers_address', array('customer_id' => $bill_customer_id, ''), '');
						if (isset($bill_BillingAddress)) {
							$BillingAddress = $bill_BillingAddress;
						}
					}
				}
			} elseif (isset($customer_email_alternate_shop) & !empty($customer_email_alternate_shop)) {
				$customers_invoiceData_alt_cusVar = $this->ci->CommonModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_email_alternate_shop), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
				if (isset($customers_invoiceData_alt_cusVar) && !empty($customers_invoiceData_alt_cusVar)) {
					$payment_term = $customers_invoiceData_alt_cusVar->payment_term;
				}
			}
			if(empty($customer_gst_no) && empty($customer_company)) {
				$bill_customer_company_name_gst = $this->ci->CommonModel->getSingleShopDataByID('customers', array('id' => $bill_customer_id), 'company_name,gst_no,CONCAT(first_name, " ", last_name) as customer_name');
				if (isset($bill_customer_company_name_gst)) {
					$customer_company = $bill_customer_company_name_gst->company_name;
					$customer_gst_no = $bill_customer_company_name_gst->gst_no;
				}
			}

		}

		$invoicing_one = $this->ci->WebshopOrdersModel->insertData('invoicing', [
			'invoice_no' => $invoice_no,
			'customer_first_name' => $orderData->customer_firstname,
			'customer_last_name' => $orderData->customer_lastname,
			'customer_id' => $orderData->customer_id,
			'customer_email' => $orderData->customer_email,
			'shop_gst_no' => '',
			'bill_customer_first_name' => $BillingAddress->first_name,
			'bill_customer_last_name' => $BillingAddress->last_name,
			'bill_customer_company_name' => $customer_company,
			'bill_customer_gst_no' => $customer_gst_no,
			'bill_customer_id' => $bill_customer_id,
			'bill_customer_email' => $bill_customer_email,
			'invoice_order_nos' => $order_id,
			'invoice_order_type' => '1',
			'invoice_subtotal' => 0,
			'invoice_tax' => 0,
			'invoice_grand_total' => 0,
			'currency' => $orderData->currency_code_session,
			'currency_conversion_rate' => $orderData->currency_conversion_rate ?? 1,
			'language_code' => $orderData->language_code,
			'billing_address_line1' => $BillingAddress->address_line1,
			'billing_address_line2' => $BillingAddress->address_line2,
			'billing_city' => $BillingAddress->city,
			'billing_state' => $BillingAddress->state,
			'billing_country' => $BillingAddress->country,
			'billing_pincode' => $BillingAddress->pincode,
			'ship_first_name' => $ShippingAddress->first_name,
			'ship_last_name' => $ShippingAddress->last_name,
			'ship_address_line1' => $ShippingAddress->address_line1,
			'ship_address_line2' => $ShippingAddress->address_line2,
			'ship_city' => $ShippingAddress->city,
			'ship_state' => $ShippingAddress->state,
			'ship_country' => $ShippingAddress->country,
			'ship_pincode' => $ShippingAddress->pincode,
			'invoice_date' => $invoice_date,
			'invoice_due_date' => $invoice_due_date,
			'invoice_term' => $payment_term ?? 0,
			'payment_charges' => $orderData->payment_final_charge,
//			'voucher_amount' => $orderData->voucher_amount,
			'voucher_amount' => 0,
			'shipping_charges' => $orderData->shipping_amount,
			'created_at' => time(),
			'ip' => $_SERVER['REMOTE_ADDR']
		]);

		if ($invoicing_one !== false) {
			$invoicedata = $this->ci->WebshopOrdersModel->get_invoicedata_by_id($invoicing_one);
			$invoice_no_update = ['value' => $invoice_next_no];
			$where_invoice_arr = ['identifier' => 'invoice_next_no'];

			$this->ci->WebshopOrdersModel->updateData('custom_variables', $where_invoice_arr, $invoice_no_update);
			$invoice_sales_order = ['invoice_id' => $invoicing_one, 'invoice_date' => $invoice_date, 'invoice_flag' => 1];
			$where_sales_order_arr = ['order_id' => $order_id];
			$this->ci->WebshopOrdersModel->updateData('sales_order', $where_sales_order_arr, $invoice_sales_order);

			if ((int) $this->ci->session->userdata('ShopID') === 1) {
				$balancedAmount = 0;

				$resultHsn = $this->ci->CommonModel->getHsncodeIdByShopId($this->ci->session->userdata('ShopID'));
				$hsnMainId = '';
				if ($resultHsn) {
					$hsnMainId = $resultHsn->id;
				}

				$invoice_id = $invoicing_one;
				$invoice_ship_state = $invoicedata->ship_state;

				$invoice_invoice_order_nos = $invoicedata->invoice_order_nos;
				$voucher_amount = $invoicedata->voucher_amount;
				$voucher_amount_used = 0;
				$voucher_amount_remain = 0;

				$shipto_state_code1 = $this->ci->CommonModel->get_states($invoice_ship_state) ?? '';

				if (isset($invoice_invoice_order_nos)) {
					$order_ids = $invoicedata->invoice_order_nos;
					$invoice_order_id = explode(",", $order_ids);
				}

				if ($invoicedata->voucher_amount > 0.00) {
					$voucher_used = $this->ci->WebshopOrdersModel->get_invoice_sum_voucher_amount_by_id($invoice_order_id, 1);
					if (isset($voucher_used) && $voucher_used[0]->total_used_voucher_amount > 0.00) {
						$voucher_amount = $invoicedata->voucher_amount - $voucher_used[0]->total_used_voucher_amount;
					}
				}

				$webshoporderData_item = $this->ci->WebshopOrdersModel->getOrder_multi_Items($invoice_order_id);
				$payment = $this->ci->SalesOrderPaymentModel->get_payments(['order_id', $order_id])[0] ?? null;
				$payment_method = $payment->payment_method ?? null; // user type login guest

				if (isset($webshoporderData_item)) {
					$taxArray = [];
					$sumArray = [];
					$ItemRowTotal_Sum = 0;

					foreach ($webshoporderData_item as $Items) {
						if((int) $Items->qty_scanned === 0){
							continue;
						}

						$order_id = $Items->order_id;
						$order_coupon_code = $Items->order_coupon_code;
						$order_create_date = $Items->created_at;
						$product_name = $Items->product_name;
						$product_id = $Items->product_id;
						$product_barcode = $Items->barcode;
						$product_variants = $Items->product_variants;
						$product_sku = $Items->sku;
						$product_qty = $Items->qty_scanned;
						$product_type = $Items->product_type;
						if ($Items->parent_product_id !== 0) {
							$product_main_id = $Items->parent_product_id;
						} else {
							$product_main_id = $Items->product_id;
						}
						$product_category = $this->ci->CommonModel->getProductsMaintCategoryNames($product_main_id);// product catego

						$product_hsn_code = '';
						$parent_product_id = $Items->parent_product_id;// product id
						if ($hsnMainId) {
							if ($product_type === 'conf-simple') {
								$FinalproductID = $parent_product_id;
							} else {
								$FinalproductID = $product_id;
							}

							$shopProductAttributes = $this->ci->CommonModel->getSingleShopDataByID('products_attributes', array('product_id' => $FinalproductID, 'attr_id' => $hsnMainId), '*');

							if ($shopProductAttributes) {
								$product_hsn_code = $shopProductAttributes->attr_value;
							}
						}

						$original_product_price_incl_tax = round($Items->price,2);
						$original_product_price_incl_tax -= round($Items->discount_amount,2);

						$ItemQty = $Items->qty_scanned;
						$ItemTaxAmount = 0;
						$ItemTaxPercent = $Items->tax_percent;

						if ($ItemTaxPercent > 0.00 && $original_product_price_incl_tax > 0.00) {
							$pro_price_excl_tax = $original_product_price_incl_tax - round($Items->tax_amount,2);
							$ItemTaxAmount = round($Items->tax_amount,2);
							$ItemRowTaxAmount = ($ItemTaxAmount * $ItemQty);
						} else {
							$pro_price_excl_tax = $original_product_price_incl_tax;
							$ItemTaxPercent = 0;
							$ItemRowTaxAmount = 0;
						}

						$ItemRowTotal = $pro_price_excl_tax * $ItemQty;

						$ItemRowTotal_Sum += $ItemRowTotal;
						$total_amount_including_gst = $ItemRowTaxAmount + $ItemRowTotal;

						if (!array_key_exists($ItemTaxPercent, $taxArray)) {
							$taxArray[$ItemTaxPercent] = [];
							$sumArray[$ItemTaxPercent] = ['final_tax_amount' => 0, 'final_price' => 0];
						}

						$sumArray[$ItemTaxPercent]['final_tax_amount'] += $ItemRowTaxAmount;
						$sumArray[$ItemTaxPercent]['final_price'] += $ItemRowTotal;

						$this->ci->WebshopOrdersModel->insertData('invoicing_details', [
							'invoice_id' => $invoice_id,
							'order_id' => $order_id,
							'order_date' => $order_create_date,
							'product_name' => $product_name,
							'product_id' => $product_id,
							'product_hsn_code' => $product_hsn_code,
							'product_barcode' => $product_barcode,
							'product_variants' => $product_variants,
							'product_category' => $product_category,
							'product_sku' => $product_sku,
							'product_qty' => $product_qty,
							'product_price' => round($pro_price_excl_tax,2),
							'place_of_supply' => $shipto_state_code1,
							'gst_rates_applicable' => $ItemTaxPercent,// percentage
							'gst_amount' => round($ItemTaxAmount,2), //item tax
							'gst_row_amount' => round($ItemRowTaxAmount,2),// item tax * qty
							'total_amount_excluding_gst' => round($ItemRowTotal,2),
							'total_amount_including_gst' => round($total_amount_including_gst,2), // gst_row_amount + total_amount_excluding_gst
							'created_by' => $this->ci->session->userdata('LoginID'),
							'created_at' => time(),
							'ip' => $_SERVER['REMOTE_ADDR']
						]);
					}

					[$sumArray, $shippingRowTotal_Sum_ex_tax] = $this->processShippingCharges($orderData, $sumArray, $invoice_id, $order_id, $order_create_date, $shipto_state_code1);
					[$sumArray, $codRowTotal_Sum_ex_tax] = $this->processPaymentCharges($invoicedata, $Items, $sumArray, $invoice_id, $order_id, $order_create_date, $shipto_state_code1);
					[$sumArray, $voucher_amount_ex_tax] = $this->processVoucher($orderData, $invoice_id, $order_create_date, $sumArray);
				}

				$subtotalsumamount = $ItemRowTotal_Sum + $codRowTotal_Sum_ex_tax + $shippingRowTotal_Sum_ex_tax - $voucher_amount_ex_tax;

				if (isset($sumArray)) {
					$finaltaxAmountSum = 0;
					foreach ($sumArray as $sumArrayValue) {
						$final_TaxAmount = $sumArrayValue['final_tax_amount'];
						$finaltaxAmountSum += $final_TaxAmount;
					}
				}

				$final_sub_total_price_tax = $subtotalsumamount + ($finaltaxAmountSum ?? 0);

				if ($final_sub_total_price_tax < $voucher_amount) {
					$voucher_amount_remain = $voucher_amount - $final_sub_total_price_tax;
					$voucher_amount_used = $voucher_amount - $voucher_amount_remain;
					$final_total_price_tax = 0.00;
				} else {
					$final_total_price_tax = ($final_sub_total_price_tax) - $voucher_amount;
				}

				if ($payment_method === 'cod' || $payment_method === 'via_transfer' || empty($payment_method)) {
					$balancedAmount = $final_total_price_tax;
				}

				if((float)$orderData->grand_total !== $final_total_price_tax){
					$difference = round((float)$orderData->grand_total - (float) $final_total_price_tax,2);

					if(abs($difference) < ((float)$orderData->grand_total * 0.005) && $final_total_price_tax > 0 ){
						$final_total_price_tax = round((float)$orderData->grand_total,2);
						$finaltaxAmountSum = round($finaltaxAmountSum + $difference,2);
					}
				}

				$this->ci->WebshopOrdersModel->updateData('invoicing',
					['id' => $invoice_id],
					[
						'invoice_subtotal' => $ItemRowTotal_Sum,
						'invoice_tax' => $finaltaxAmountSum,
						'invoice_grand_total' => $final_total_price_tax,
						'voucher_used_amount' => $voucher_amount_used,
						'voucher_remain_amount' => $voucher_amount_remain,
						'invoice_balanced_amount' => $balancedAmount,
						'updated_at' => time()
					]);
			}
		}

		return $invoice_id;
	}

	private function processShippingCharges($orderData, array $sumArray, $invoice_id, $order_id, $order_create_date, string $shipto_state_code1): array
	{
		$shippingRowTotal_Sum_ex_tax = 0;
		if ($orderData->shipping_charge > 0.00) {
			$shipping_charge = round($orderData->shipping_charge,2); // actual amount
			$shipping_tax_percent = $orderData->shipping_tax_percent; //tax percentage

			$shipping_charge_RowExcTax = ($shipping_charge);
			$shipping_charge_tax_amount = round(($shipping_tax_percent / 100) * $shipping_charge,2);
			$shipping_charge_RowTaxAmount = $shipping_charge_tax_amount;
			$shipping_ItemRowTotal = $shipping_charge + $shipping_charge_RowTaxAmount;

			if (!array_key_exists($shipping_tax_percent, $sumArray)) {
				$sumArray[$shipping_tax_percent] = ['final_tax_amount' => 0, 'final_price' => 0];
			}

			$sumArray[$shipping_tax_percent]['final_tax_amount'] += $shipping_charge_RowTaxAmount;
			$sumArray[$shipping_tax_percent]['final_price'] += $shipping_charge;

			$shippingRowTotal_Sum_ex_tax += $shipping_charge_RowExcTax; //exclude tax

			$this->ci->WebshopOrdersModel->insertData('invoicing_details', [
				'invoice_id' => $invoice_id,
				'order_id' => $order_id,
				'order_date' => $order_create_date,
				'product_id' => 0,
				'product_name' => 'Shipping: ' . $orderData->ship_method_name ?? '',
				'product_sku' => 'Ship',
				'product_qty' => 1,
				'product_price' => $shipping_charge,
				'place_of_supply' => $shipto_state_code1,
				'gst_rates_applicable' => $shipping_tax_percent,// percentage
				'gst_amount' => $shipping_charge_tax_amount, //item tax
				'gst_row_amount' => $shipping_charge_RowTaxAmount,// item tax * qty
				'total_amount_excluding_gst' => $shipping_charge_RowExcTax,
				'total_amount_including_gst' => $shipping_ItemRowTotal, // gst_row_amount + total_amount_excluding_gst
				'created_by' => $this->ci->session->userdata('LoginID'),
				'created_at' => time(),
				'ip' => $_SERVER['REMOTE_ADDR']
			]);

		}
		return [$sumArray, $shippingRowTotal_Sum_ex_tax];
	}

	private function processPaymentCharges($invoicedata, $Items, $sumArray, $invoice_id, $order_id, $order_create_date, string $shipto_state_code1): array
	{
		$codRowTotal_Sum_ex_tax = 0;
		if ($invoicedata->payment_charges > 0.00) {
			if ($Items->payment_method === 'cod') {
				$paymentorderItemQty = 1;
				$payment_charge = $Items->payment_charge; // actual amount
				$payment_tax_percent = $Items->payment_tax_percent;// percentage

				$payment_charge_RowExcTax = 0;
				$payment_ItemRowTotal = 0;
				$payment_charge_tax_amount = 0;
				$payment_charge_RowTaxAmount = 0;

				if ($payment_tax_percent > 0.00 && $payment_charge > 0.00) {
					$payment_charge_RowExcTax = $payment_charge;
					$payment_charge_tax_amount = ($payment_tax_percent / 100) * $payment_charge;
					$payment_charge_RowTaxAmount = $payment_charge_tax_amount;
					$payment_ItemRowTotal = $payment_charge + $payment_charge_RowTaxAmount;
				}

				if (!array_key_exists($payment_tax_percent, $sumArray)) {
					$sumArray[$payment_tax_percent] = ['final_tax_amount' => 0, 'final_price' => 0];
				}

				$sumArray[$payment_tax_percent]['final_tax_amount'] = $sumArray[$payment_tax_percent]['final_tax_amount'] + $payment_charge_RowTaxAmount;
				$sumArray[$payment_tax_percent]['final_price'] = $sumArray[$payment_tax_percent]['final_price'] + $payment_charge;

				$codRowTotal_Sum_ex_tax = $payment_charge_RowExcTax; //include tax

				$insertinvoicingdetailsdata_cod = [
					'invoice_id' => $invoice_id,
					'order_id' => $order_id,
					'order_date' => $order_create_date,
					'product_name' => 'COD Charges',
					'product_sku' => 'COD',
					'product_qty' => $paymentorderItemQty,
					'product_price' => $invoicedata->payment_charges,
					'place_of_supply' => $shipto_state_code1,
					'gst_rates_applicable' => $payment_tax_percent,// percentage
					'gst_amount' => $payment_charge_tax_amount, //item tax
					'gst_row_amount' => $payment_charge_RowTaxAmount,// item tax * qty
					'total_amount_excluding_gst' => $payment_charge_RowExcTax,
					'total_amount_including_gst' => $payment_ItemRowTotal, // gst_row_amount + total_amount_excluding_gst
					'created_by' => $this->ci->session->userdata('LoginID'),
					'created_at' => time(),
					'ip' => $_SERVER['REMOTE_ADDR']
				];
				$this->ci->WebshopOrdersModel->insertData('invoicing_details', $insertinvoicingdetailsdata_cod);
			}
		}
		return [$sumArray, $codRowTotal_Sum_ex_tax];
	}

	private function processVoucher($orderData, $invoice_id, $order_create_date, $sumArray)
	{
		if($orderData->voucher_amount === null || (float) $orderData->voucher_amount === 0.0){
			return [$sumArray, 0];
		}

		$vat_percent = (int) $orderData->shipping_tax_percent;
		$payment_ItemRowTotal = $orderData->voucher_amount;
		$gst_amount = $orderData->voucher_amount / (1 + ($vat_percent / 100)) * ($vat_percent / 100);
		$product_price = $payment_ItemRowTotal - $gst_amount;


		if (!array_key_exists($vat_percent, $sumArray)) {
			$sumArray[$vat_percent] = ['final_tax_amount' => 0, 'final_price' => 0];
		}

		$sumArray[$vat_percent]['final_tax_amount'] = $sumArray[$vat_percent]['final_tax_amount'] - $gst_amount;
		$sumArray[$vat_percent]['final_price'] = $sumArray[$vat_percent]['final_price'] - $payment_ItemRowTotal;



		$insertinvoicingdetailsdata_cod = [
			'invoice_id' => $invoice_id,
			'order_id' => $orderData->order_id,
			'order_date' => $order_create_date,
			'product_name' => 'Voucher: ' . $orderData->voucher_code . " ({$orderData->voucher_amount} €)",
			'product_sku' => 'VOUCHER',
			'product_qty' => 1,
			'product_price' => $product_price * -1,
			'place_of_supply' => '',
			'gst_rates_applicable' => $vat_percent,
			'gst_amount' => $gst_amount * -1, //item tax
			'gst_row_amount' => $gst_amount * -1,// item tax * qty
			'total_amount_excluding_gst' => $product_price * -1,
			'total_amount_including_gst' => $payment_ItemRowTotal * -1, // gst_row_amount + total_amount_excluding_gst
			'created_by' => $this->ci->session->userdata('LoginID'),
			'created_at' => time(),
			'ip' => $_SERVER['REMOTE_ADDR']
		];

		$this->ci->WebshopOrdersModel->insertData('invoicing_details', $insertinvoicingdetailsdata_cod);
//
		return [$sumArray, $product_price];
	}

}
