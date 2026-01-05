<?php
//balanced amount
$balancedAmount=0;

// Ship To

//$payment_charges=$invoicedata->payment_charges;

$invoice_invoice_date = $invoicedata->invoice_date ? date(DATE_PIC_FM, $invoicedata->invoice_date) : '';
$invoice_invoice_due_date = $invoicedata->invoice_due_date ? date(DATE_PIC_FM, $invoicedata->invoice_due_date) : '';

$currency=$invoicedata->currency;

if(!empty($invoicedata->invoice_order_nos)){
	$order_ids = $invoicedata->invoice_order_nos;
	$invoice_order_id = explode(",", $order_ids);

	if(count($invoice_order_id) === 1){
		$order_number = $this->WebshopOrdersModel->getOrderNumberById($invoice_order_id[0]);
	}
}

$payments = $this->WebshopOrdersModel->getInvoicePayments($invoicedata->invoice_order_nos);

if(count($payments) === 0){
	$payment_method = '';
	$payment_name=''; // user type login guest
} else {
	$payment_method = $payments[0]->payment_method ?? '';
	$payment_name = $payments[0]->payment_method_name ?? '';
}

// user detais
$invoice_bottom_message_value='';
// web shop data  setting page
foreach($custom_variables as $key=>$val)
{
	if($val['identifier']=='invoice_add_field1'){
		$invoice_add_field1_name=$val['name'];
		$invoice_add_field1_value=$val['value'];
	}

	if($val['identifier']=='invoice_add_field2'){
		$invoice_add_field2_name=$val['name'];
		$invoice_add_field2_value=$val['value'];
	}

	if($val['identifier']=='invoice_bottom_message') {
		$invoice_bottom_message_name = $val['name'];
		$invoice_bottom_message_value = $val['value'];
	}
}

// new code
$vat_n_translation='VAT';
if(isset($shop_id) && !empty($shop_id) && isset($user_shop_details)) {
	$vat_n_translation=$user_shop_details->vat_n_translation?: 'VAT';
}

$shop_vat_no = 'BG206792586';
$vat_message = '';

if((float) $invoicedata->invoice_tax === 0.0) {
	$shop_vat_no = $invoicedata->ship_country === 'BG' ? 'BG206792586' : 'NL826682236B01';

	if ($invoicedata->ship_country === 'NL') {
		$vat_message = '(BTW verlegd)';
	} elseif (in_array($invoicedata->ship_country, ['BE', 'CZ', 'DK', 'DE', 'EE', 'IE', 'GR', 'ES', 'FR', 'HR', 'IT', 'CY', 'LV', 'LT', 'LU', 'HU', 'MT', 'AT', 'PL', 'PT', 'RO', 'SI', 'SK', 'FI', 'SE'])) {
		$vat_message = '(Intracommunity Delivery)';
	} else {
		$vat_message = '(Export Delivery)';
	}

} else {
	$shop_vat_no = $invoicedata->ship_country === 'NL' ? 'NL826682236B01' : 'BG206792586';
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Invoice</title>
	<style>
		body{font-size: 14px;
			font-family: helvetica;
			color: #000000;padding:10px;}
		.table-block{
			border: 1px solid #e0e0e0;
			width: 100%;
			max-width: 680px;
			margin: 0 auto;
			background: #fff;
			font-size: 14px;
			font-family: helvetica;
			color: #000000;
		}
		p{margin-bottom:0; line-height:12px; font-weight: normal;
			font-size: 13px;
			line-height: 12px;
			margin-bottom: 0;
			margin-top: 5px;
		}
		p.address{
			font-weight: normal;
			font-size: 13px;
			line-height: 12px;
			margin-bottom: 0;
			margin-top: 5px;
		}
		.supply-table th{
			font-family: helvetica;
			font-weight: 700;
			padding: 10px 13px;
			background: #f1f1f1;
			text-transform: uppercase;
			text-align: left;
			font-size: 11px;
		}
		.supply-table td{
			font-family: helvetica;
			font-weight: normal;
			border-collapse: collapse;
			vertical-align: top;
			padding: 10px 13px;
			margin: 0;
			border-top: 1px solid #ebebeb;
			font-size: 12px;
		}
		thead:before, thead:after { display: none; }
		tbody:before, tbody:after { display: none; }
		table, tr, td, th, tbody, thead, tfoot {
			/*page-break-inside: avoid !important;*/
		}
	</style>

</head>

<body>

<!-- table 1 start -->
<table style="width:680px;max-width:680px;background:#fff;font-size:14px;color:#000000;margin-bottom:10px;" >
	<!-- comment -->
		<tr>
			<td style="padding:10px 0 0px 0">
				<table style="width: 100%; border-bottom: 2px solid #DDD">
					<tr>
						<td style="width: 33%; text-align: left; vertical-align: top; padding-bottom: 10px;">
							<p style="font-size:0.7rem; line-height: 0.9rem">
								<strong><?= $user_shop_details->company_name ?? '' ?></strong><br>
								<?=  $user_shop_details->bill_address_line1 ?? '' ?>
								<?=  (!empty($user_shop_details->bill_address_line2)) ? '<br>' . $user_shop_details->bill_address_line2 : '' ?><br>
								<?= $user_shop_details->bill_pincode ?? '' ?> <?= $user_shop_details->bill_city ?? '' ?>, Bulgaria<br>
								<?=  $shop_vat_no ?? '' ?>
							</p>
						</td>
						<td style="width: 33%; text-align: center">
							<img width="" style="max-width:120px;" alt="" border="0" src="https://fbc-shop1.s3.amazonaws.com/zumbawear%20europe%20logo-600.png" class="CToWUd">
						</td>
						<td style="width: 33%;text-align: right; font-size: 2rem; line-height: 1.2rem;">
							INVOICE<br>
							<span style="font-size: 1rem"># <?= $invoicedata->invoice_no ?? '' ?></span>
						</td>
					</tr>
				</table>
			</td>
		</tr>

	<!-- comment end -->

	<tr>
		<td style="width:671px; padding-top: 24px; padding-bottom: 16px;">
			<table style="width:680px">
				<tbody>
				<tr>
					<td style="width:150px;vertical-align: top;padding-bottom:5px; padding-right:15px; font-size: 0.8rem; line-height:1.1rem">
						<strong> BILL TO</strong><br />
							<?= $invoicedata->bill_customer_company_name ? '<b>'.$invoicedata->bill_customer_company_name.'</b><br/>' : '' ?>
							<?= $invoicedata->bill_customer_first_name.' '.$invoicedata->bill_customer_last_name ?><br />
							<?=$invoicedata->billing_address_line1?><br/>
							<?= !empty($invoicedata->billing_address_line2) ? $invoicedata->billing_address_line2 .'<br/>' : '' ?>
							<?= $invoicedata->billing_country === 'IN' ?
									$invoicedata->billing_city .' '. $invoicedata->billing_state .'-'. $invoicedata->billing_pincode .', ' :
									$invoicedata->billing_pincode .' '. $invoicedata->billing_city .', '
							?>
							<?= $invoicedata->billing_country ?>

						<?php
						if($invoicedata->billing_state){
							$bilto_state_code = $this->CommonModel->get_states($invoicedata->billing_state);
						}
						?>

							<?php if(!empty($bilto_state_code)){ ?>
								<br>
								State Code: <?= $bilto_state_code->state_code ?>
							<?php }

							if($invoicedata->bill_customer_gst_no){
								echo '<br/>'.$vat_n_translation.':'. $invoicedata->bill_customer_gst_no;
							}
							?>


						<?php
						$shipto_state_code="";
						$shipto_state_code1="";
						if($invoicedata->ship_state){
							if($this->CommonModel->get_states($invoicedata->ship_state)){
								$shipto_state_code= $this->CommonModel->get_states($invoicedata->ship_state);
								if($shipto_state_code){
									$shipto_state_code1=$shipto_state_code->state_code; //add all
								}
							}
						}
						?>
					</td>

					<td style="width:150px;vertical-align: top;padding-bottom:5px; padding-right:15px;  font-size: 0.8rem; line-height:1.1rem">
						<strong>SHIP TO</strong><br />
						<?= $invoicedata->ship_first_name .' '. $invoicedata->ship_last_name ?><br>
						<?= $invoicedata->ship_address_line1 ?>,<br/>
						<?= !empty($invoicedata->ship_address_line2) ? $invoicedata->ship_address_line2 .',<br/>' : ''?>
						<?= $invoicedata->ship_country === 'IN' ?
								$invoicedata->ship_city .' '. $invoicedata->ship_state .'-'. $invoicedata->ship_pincode .', ' :
								$invoicedata->ship_pincode .' '. $invoicedata->ship_city .', '
						?>
						<?= $invoicedata->ship_country ?>

						<?php if(isset($shipto_state_code) && !empty($shipto_state_code)){
							?>
							State Code: <?php echo ((isset($shipto_state_code) && $shipto_state_code != null) ? $shipto_state_code->state_code : '' ); ?>
						<?php } ?>
					</td>

					<td style="width:150px;vertical-align: top;padding-bottom:5px;  font-size: 0.8rem; line-height:1.1rem">
						<table>
							<tr>
								<td style="text-align: right; width: 110px;"><strong>DATE</strong></td>
								<td><?= $invoice_invoice_date ?? '' ?></td>
							</tr>
							<tr>
								<td style="text-align: right; width: 110px;"><strong>DUE DATE</strong></td>
								<td><?php  echo ((isset($invoice_invoice_due_date) && $invoice_invoice_due_date != null) ? $invoice_invoice_due_date : '' ); ?></td>
							</tr>
							<tr>
								<td style="text-align: right; width: 110px;"><strong>TERMS</strong></td>
								<td><?= $invoicedata->invoice_term > 0 ? $invoicedata->invoice_term .' Days' : 'Due on receipt' ?></td>
							</tr>
							<?php if(!empty($order_number)) { ?>
							<tr>
								<td style="text-align: right; width: 110px;"><strong>ORDER</strong></td>
								<td><?= $order_number ?></td>
							</tr>
							<?php } ?>
						</table>
					</td>

				</tr>
				</tbody>
			</table>
		</td>
	</tr>
</table>



<table width="680" style="width:680px;padding:0px 0px 10px;margin:0;">
	<thead>
	<tr><th  style="padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">
			QTY
		</th>
		<th style="padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:left;font-size:11px" >
			PRODUCT</span>
		</th>
		<th style="padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">
			RATE
		</th>
<!--		<th style="padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">-->
<!--			VAT %-->
<!--		</th>-->
		<th style="padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:right;font-size:11px">
			AMOUNT
		</th>
	</tr>
	</thead>
	<tbody>
	<?php
	$webshoporderData_item = $this->WebshopOrdersModel->getInvoiceItems($invoicedata->id);

	if(isset($webshoporderData_item)){

		$skuArray=array();
		$qtyOrderArray=array();

		$i=1;
		// tax arrray generate
		$taxArray=array();
		$sumArray=array();
		$ItemRowTotal_Sum=0;

		$shippingRowTotal_Sum=0;
		$shippingRowTotal_Sum_ex_tax=0;
		$shippingRowTotal_tax=0;
		$codRowTotal_Sum=0;
		$codRowTotal_Sum_ex_tax=0;
		$codRowTotal_tax=0;

		$total_price_sum=0;
		$total_price_tax_sum=0;

		foreach ($webshoporderData_item as $Items) {
			// @todo: coupon code in ivnoice (if not alrady and necessary)
			$order_coupon_code=$Items->order_coupon_code ?? '';

			$ItemPrice = $Items->product_price * $invoicedata->currency_conversion_rate;
			$ItemTaxPercent= $Items->gst_rates_applicable;

			$ItemTaxPercent = number_format($ItemTaxPercent, 0, ',','.');


			$ItemRowTotal= $ItemPrice  * $Items->product_qty;



			$ItemRowTotal_Sum += round($ItemRowTotal,2);
			$gst_row_amount=$Items->gst_row_amount;
			$total_amount_including_gst=$gst_row_amount + $ItemRowTotal;

			if(!array_key_exists($ItemTaxPercent, $taxArray)){
				$taxArray[$ItemTaxPercent]=array();
				$sumArray[$ItemTaxPercent]=array('final_tax_amount'=>0,'final_price'=>0);
			}

			$sumArray[$ItemTaxPercent]['final_tax_amount']=$sumArray[$ItemTaxPercent]['final_tax_amount'] + $Items->gst_row_amount;
			$sumArray[$ItemTaxPercent]['final_price']=$sumArray[$ItemTaxPercent]['final_price'] + $ItemRowTotal;

			$variant_data='';
			if(isset($Items->product_variants) && $Items->product_variants!=''){
				$variants=json_decode($Items->product_variants, true);
				if(isset($variants) && count($variants)>0){
					foreach($variants as $pk=>$single_variant){
						if($pk > 0){ $variant_data.= ' - ';}
						foreach($single_variant as $key=>$val){
							$variant_data.=$val;
						}
					}
				}
			}
			if($variant_data){ $variant_data='('.$variant_data.')';}


			//exit();
			?>
			<tr>
				<td style="text-align:right;vertical-align:top;padding:5px 5px;margin:0;border-top:1px solid #ebebeb; text-align:center;">
					<span style=";font-size: 12px;"><?= $Items->product_qty ?></span>
				</td>
				<td style="text-align:left;vertical-align:top;padding:5px 5px;margin:0;border-top:1px solid #ebebeb">
					<span style="font-size: 12px;">
						<?=$Items->product_name?> <?=$variant_data?>
						<span style="font-size: 10px; color: #666;"><?=!empty($Items->sku) ? '<br>' . $Items->sku  : ''?><?=!empty($Items->product_barcode) ? ' - ' . $Items->product_barcode  : ''?></span>
					</span>
				</td>
				<td style="text-align:right;vertical-align:top;padding:5px 5px;margin:0;border-top:1px solid #ebebeb;  text-align:center;">
					<span style=";font-size: 12px;"><?=number_format($ItemPrice,2, ',','.')?></span>
				</td>
				<td style="text-align:right;vertical-align:top;padding:5px 5px;margin:0;border-top:1px solid #ebebeb">
					<span style=";font-size: 12px;"><?=number_format($ItemRowTotal,2, ',','.')?></span>
				</td>

			</tr>
			<?php

			$i++;
		}

//		if($payment_charges > 0.00){
//			if($payment_method=='cod' && $payment_charges > 0.00){
//				$paymentorderItemQty=1;
//				$payment_charge=$Items->payment_charge; // actual amount
//				$payment_tax_percent=$Items->payment_tax_percent;// percentage
//				$payment_tax_amount=$Items->payment_tax_amount; // aftercalculation only tax amount
//				$payment_final_charge=$Items->payment_final_charge; // including tax amount
//				$paymentChargeItemRowTotal=$payment_charge;
//
//				if($payment_tax_percent > 0.00 && $payment_charge > 0.00) {
//					$payment_charge_RowExcTax = ($payment_charge);
//					$payment_charge_tax_amount = ($payment_tax_percent / 100) * $payment_charge;
//					$payment_charge_RowTaxAmount = ($payment_charge_tax_amount);
//					$payment_ItemRowTotal = $payment_charge + $payment_charge_RowTaxAmount;
//				} else{
//					$payment_charge_RowExcTax=0;
//					$payment_ItemRowTotal=0;
//					$payment_charge_tax_amount=0;
//					$payment_charge_RowTaxAmount =0;
//				}
//				if(!array_key_exists($payment_tax_percent, $taxArray)){
//					$taxArray[$payment_tax_percent]=array();
//					$sumArray[$payment_tax_percent]=array('final_tax_amount'=>0,'final_price'=>0);
//
//				}
//
//				$sumArray[$payment_tax_percent]['final_tax_amount']=$sumArray[$payment_tax_percent]['final_tax_amount'] + $payment_charge_RowTaxAmount;
//				$sumArray[$payment_tax_percent]['final_price']=$sumArray[$payment_tax_percent]['final_price'] + $payment_charge;
//
//				$codRowTotal_Sum += $payment_ItemRowTotal; //include tax
//				$codRowTotal_Sum_ex_tax += $payment_charge_RowExcTax; //include tax
//				$codRowTotal_tax += $payment_charge_RowTaxAmount; //actual cod without tax
//
//			}
//			?>
<!--			<tr>-->
<!--				<td style="text-align:left;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb">-->
<!--					<span style="; font-size: 12px;">COD Charge</span>-->
<!--				</td>-->
<!--				<td style="text-align:center;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb; ">-->
<!--					<span style=";font-size: 12px;">COD</span>-->
<!--				</td>-->
<!--				<td style="text-align:center;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb">-->
<!--					<span style=";font-size: 12px;">--><?//=$payment_tax_percent?><!--%</span>-->
<!--				</td>-->
<!---->
<!--				<td style="text-align:right;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb; text-align:center;">-->
<!--					<span style=";font-size: 12px;">--><?//=$paymentorderItemQty?><!--</span>-->
<!--				</td>-->
<!--				<td style="text-align:right;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb;  text-align:center;">-->
<!--					<span style=";font-size: 12px;">--><?//=number_format($payment_charge,2, ',','.')?><!--</span>-->
<!--				</td>-->
<!--				<td style="text-align:right;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb">-->
<!--					<span style=";font-size: 12px;">--><?//=number_format($paymentChargeItemRowTotal,2, ',','.')?><!--</span>-->
<!--				</td>-->
<!--			</tr>-->
<?php
//		}
	}
?>

	</tbody>
</table>


<table style="width:680px;padding:0;margin:0;border-top:1px dashed #c3ced4;">
	<tbody>
	<tr>
		<td style="vertical-align:top;padding:30px 0px;margin:0;text-align:left;line-height:20px; font-size: 0.75rem; line-height: 0.9rem" width="40%">
			<?php if($payment_method==='cod' || $payment_method==='via_transfer' || empty($payment_method)){?>
			<strong>Bank details:</strong><br>
				<?= !empty($user_shop_details->bank_name) ? 'BANK NAME : ' . $user_shop_details->bank_name : ''  ?>
				<?= !empty($user_shop_details->bank_branch) ? ' - ' . $user_shop_details->bank_branch : '' ?><br>

				A/C NAME : <?php  echo ((isset($user_shop_details->bank_acc_name) && $user_shop_details->bank_acc_name != null) ? $user_shop_details->bank_acc_name : '' ); ?><br>
				<?php if($currency !== 'USD') {
					if(!empty($invoice_add_field1_name) && !empty($invoice_add_field1_value)){ ?>
						<?= $invoice_add_field1_name.':' ?> <?=  $invoice_add_field1_value ?><br>
					<?php }
				} else {
					if(!empty($invoice_add_field2_name) && !empty($invoice_add_field2_value)){ ?>
						<?= $invoice_add_field2_name.':' ?> <?=  $invoice_add_field2_value ?><br>
					<?php }
				}
				?>
				BIC/SWIFT : <?php  echo ((isset($user_shop_details->bic_swift) && $user_shop_details->bic_swift != null) ? $user_shop_details->bic_swift : '' ); ?><br>
				<?php } ?>
		</td>
		<td style="vertical-align:top;padding:20px 0px;margin:0;text-align:right;line-height:20px; padding-left: 25px;" width="60%">
			<table style="width:100%;padding:0;margin:0">
				<tbody>

				<?php
				// subtotal all amount
				$subtotalsumamount=$ItemRowTotal_Sum + $codRowTotal_Sum_ex_tax + $shippingRowTotal_Sum_ex_tax;

				?>
				<tr style="padding-bottom:5px">
					<td  align="right" style="padding:5px 9px;vertical-align:top;margin:0;width: 60%;     text-align: left;">
						Subtotal
					</td>
					<td align="right" style="padding:3px 9px;vertical-align:top;margin:0;">
						<?=$currency.' '.number_format($subtotalsumamount,2, ',','.')?>
					</td>
				</tr>
				<?php
				$total_price_tax_sum=0;
				if(isset($sumArray)){
					// $taxType1='CGST';
					$taxType1='IGST';
					$taxType2='';
					$finaltaxAmountSum=0;
					foreach ($sumArray as $sumArrayKey => $sumArrayValue) {
						$taxPercentage=$sumArrayKey;
						$final_TaxAmount=$sumArrayValue['final_tax_amount'];
						$finalPrice=$sumArrayValue['final_price'];
						$finaltaxAmountSum += $final_TaxAmount;
						?>
						<tr style="padding-bottom:5px">
							<td
								style="padding:5px 9px;vertical-align:top;margin:0;width: 60%;  text-align: left;">
								<?php echo 'VAT ' . $taxPercentage . '%'; ?>
								<span style="font-size: 0.8em; color: #666"><?= $vat_message ?? '' ?></span>
							</td>
							<td align="right"
								style="padding:5px 9px;vertical-align:top;margin:0;">
								<?= $currency . ' ' . number_format($invoicedata->invoice_tax * $invoicedata->currency_conversion_rate, 2, ',','.') ?>
							</td>
						</tr>
						<?php
					}

				}

				?>

				<tr style="padding-bottom:5px">
					<td style="padding:5px 9px;vertical-align:top;margin:0; text-align: left; font-weight: bold">
						INVOICE TOTAL
					</td>
					<td style="padding:3px 9px;vertical-align:top;margin:0;font-weight: bold; text-align: right;">
						<?=$currency.' '.number_format($invoicedata->invoice_grand_total * $invoicedata->currency_conversion_rate, 2, ',','.')?>
					</td>
				</tr>
				<?php
				if($invoicedata->voucher_amount >0.00){
					$displayvoucher_amount=0;
					if($invoicedata->invoice_grand_total == $invoicedata->voucher_amount){
						$displayvoucher_amount=$invoicedata->voucher_amount;
					}elseif($invoicedata->invoice_grand_total < $invoicedata->voucher_amount){
						$displayvoucher_amount = $invoicedata->invoice_grand_total;
					}else{
						$displayvoucher_amount = $invoicedata->voucher_amount;
					}
					?>
					<tr style="padding-bottom:5px">
						<td  align="right" style="padding:5px 9px;vertical-align:top;margin:0;width: 60%;     text-align: left;">
							Paid voucher
						</td>
						<td align="right" style="padding:3px 9px;vertical-align:top;margin:0;">
							<?=$currency.' - '.number_format($displayvoucher_amount,2, ',','.')?>
						</td>
					</tr>
					<?php


				}//voucher end
				if($payment_method=='cod' || $payment_method=='via_transfer' || $payment_method==''){
					$balancedAmount=$invoicedata->invoice_grand_total;
					?>
					<tr>
						<td  align="right" style="padding:3px 9px;vertical-align:top;margin:0;width: 60%;     text-align: left;">
							BALANCE DUE
						</td>
						<td align="right" style="padding:3px 9px;vertical-align:top;margin:0;font-weight: bold;">
							<?=$currency.' '.number_format($invoicedata->invoice_grand_total * $invoicedata->currency_conversion_rate, 2, ',','.');?>
						</td>
					</tr>
					<?php } else {
					$balancedAmount=0;
					?>
					<tr>
						<td align="left" style="padding:3px 9px;vertical-align:top;margin:0; text-align:left;font-size:14px;">
							Payment received through <?= $payment_name ?>
						</td>
						<td align="right" style="padding:3px 9px;vertical-align:top;margin:0;">
							<?= $currency.' '.number_format($invoicedata->invoice_grand_total * $invoicedata->currency_conversion_rate, 2, ',','.') ?>
						</td>
					</tr>
					<tr>
						<td  align="right" style="padding:3px 9px;vertical-align:top;margin:0;width: 60%;     text-align: left;">
							BALANCE DUE
						</td>
						<td align="right" style="padding:3px 9px;vertical-align:top;margin:0;">
							<span style=""><?=$currency.' 0'?></span>
						</td>
					</tr>
					<tr>
						<td  colspan="2" align="right" style="padding:3px 9px;vertical-align:top;margin:0; text-align:left;font-size:14px;">
							<?php
							//echo $this->CommonModel->getIndianCurrencytoText($invoicedata->invoice_grand_total); ?>

						</td>
					</tr>
				<?php } ?>

				</tbody>
			</table>
		</td>
	</tr>
	</tbody>
</table>

<table cellspacing="2" style="width:100%;border-top:1px solid #ccc">
	<tr>
		<td style="height:45px;padding:10px 13px;text-align:center; line-height: 1.5rem">
			<p><?= $invoice_bottom_message_value ?? '' ?></p>
			For any questions regarding your invoice, contact: billing@zumbawear.eu<br>
			<span style="font-size: 12px;">If VAT has not been applied to your purchase it is either because
				(1) a valid VAT number was provided for this purchase; or
				(2) you are shipping to a country outside of the European Union (EU).
			</span>
		</td>
	</tr>
</table>
</body>
</html>
