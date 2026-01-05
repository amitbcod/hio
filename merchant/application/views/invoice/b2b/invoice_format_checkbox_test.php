<?php

	// hsn code main database start
	$resultHsn=$this->CommonModel->getHsncodeIdByShopId($shop_id);
	$hsnMainId='';
	if($resultHsn){
		$hsnMainId=$resultHsn->id;
	}
	// end
	// invoice data
	$invoice_id=$invoicedata->id;
	$invoice_no=$invoicedata->invoice_no;
	$invoice_customer_first_name=$invoicedata->customer_first_name	;
	$invoice_customer_last_name=$invoicedata->customer_last_name;
	$invoice_customer_id=$invoicedata->customer_id;
	$invoice_customer_email=$invoicedata->customer_email;
	$invoice_shop_id=$invoicedata->shop_id;
	// b2b tax_exampted
	$tax_exampted='';
	if($invoice_shop_id){
		$taxExampted=$this->CommonModel->getSingleShopDataByID('b2b_customers',array('shop_id'=>$invoice_shop_id),'tax_exampted');
		if(isset($taxExampted->tax_exampted)){
			$tax_exampted=$taxExampted->tax_exampted;
		}else{
			$tax_exampted='';
		}
	}



	$invoice_shop_webshop_name=$invoicedata->shop_webshop_name;
	$invoice_shop_company_name=$invoicedata->shop_company_name;

	$invoice_shop_gst_no=$invoicedata->shop_gst_no;
	//BILL TO
	$invoice_bill_customer_first_name=$invoicedata->bill_customer_first_name;
	$invoice_bill_customer_last_name=$invoicedata->bill_customer_last_name;
	$bill_customer_name= $invoicedata->bill_customer_first_name.' '.$invoicedata->bill_customer_last_name;
	$invoice_bill_customer_id=$invoicedata->bill_customer_id;
	$invoice_bill_customer_email=$invoicedata->bill_customer_email;
	$invoice_billing_address_line1=$invoicedata->billing_address_line1;
	$invoice_billing_address_line2=$invoicedata->billing_address_line2;
	$invoice_billing_city=$invoicedata->billing_city;
	$invoice_billing_state=$invoicedata->billing_state;
	$invoice_billing_country=$invoicedata->billing_country;
	$invoice_billing_pincode=$invoicedata->billing_pincode;

	// Ship To
	$invoice_ship_address_line1=$invoicedata->ship_address_line1;
	$invoice_ship_address_line2=$invoicedata->ship_address_line2;
	$invoice_ship_city=$invoicedata->ship_city;
	$invoice_ship_state=$invoicedata->ship_state;
	$invoice_ship_country=$invoicedata->ship_country;
	$invoice_ship_pincode=$invoicedata->ship_pincode;

	$invoice_invoice_order_nos=$invoicedata->invoice_order_nos;
	$invoice_invoice_order_type=$invoicedata->invoice_order_type;
	$invoice_invoice_subtotal=$invoicedata->invoice_subtotal;
	$invoice_invoice_tax=$invoicedata->invoice_tax;
	$invoice_invoice_grand_total=$invoicedata->invoice_grand_total;

	// invoice data
	//echo time();
	/*echo $dateAdd=date(DATE_PIC_FM,'1623456000');
	// $daysAdd='2';
	// //print_r(date(DATE_PIC_FM,$invoicedata->invoice_date));
	// print_r(date('Y-m-d', strtotime($dateAdd. ' + '.$daysAdd.' days')));

	// echo date(strtotime(date('Y-m-d', strtotime($dateAdd. ' + '.$daysAdd.' days'))));
	exit();*/
	$invoice_invoice_date='';
	if($invoicedata->invoice_date){
		$invoice_invoice_date=date(DATE_PIC_FM,$invoicedata->invoice_date);
	}
	$invoice_invoice_due_date='';
	if($invoicedata->invoice_due_date){
		$invoice_invoice_due_date=date(DATE_PIC_FM,$invoicedata->invoice_due_date);
	}

	$invoice_invoice_term=$invoicedata->invoice_term;

	// webshop details
	$shop_name=$user_shop_details->org_shop_name;
	$shop_company_name=$user_shop_details->company_name;
	$shop_gst_no=$user_shop_details->gst_no;
	$shop_bill_address_line1=$user_shop_details->bill_address_line1;
	$shop_bill_address_line2=$user_shop_details->bill_address_line2;
	$shop_bill_city=$user_shop_details->bill_city;
	$shop_bill_state=$user_shop_details->bill_state;
	$shop_bill_pincode=$user_shop_details->bill_pincode;
	$currency=$user_shop_details->currency_code;

	// user detais
	$shop_user_email=$user_details->email;

	$invoice_logo_value='';
	$invoice_webshop_name_value='';
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

		//invoice new
		if($val['identifier']=='invoice_logo'){
		$invoice_logo_name=$val['name'];
		$invoice_logo_value=$val['value'];

		}
		if($val['identifier']=='invoice_webshop_name'){
		$invoice_webshop_name_name=$val['name'];
		$invoice_webshop_name_value=$val['value'];

		}
		if($val['identifier']=='invoice_bottom_message'){
		$invoice_bottom_message_name=$val['name'];
		$invoice_bottom_message_value=$val['value'];

		}
		//new invoice new
	}



	//exit();
?>
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<?php
	/*print_r($shopdata['webshopname']);
	exit();*/
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
          color: #888888;
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
	    page-break-inside: avoid !important;
	}
    </style>

  </head>

  <body style="margin:0;padding:0;">

      <!-- table 1 start -->
    <?php //print_r($b2borderData->company_name);?>
	<table border="0" cellpadding="5" cellspacing="5" style="border:1px solid #e0e0e0;width:680px;max-width:680px;margin:0 auto;background:#fff;font-size:14px;font-family:helvetica;color:#888888;" width="680">
		<!-- comment -->
			<?php if(!empty($invoice_logo_value) || !empty($invoice_webshop_name_value)){?>
			<tr>
				<td valign="middle" align="center" style="padding:10px 0 0px 0">
					<?php //print_r($user_web_shop_details); //exit();
						if($invoice_logo_value=='yes'){
							$site_logo = $this->encryption->decrypt($user_web_shop_details['site_logo']);
					?>
					 <a href="" style="color:#1e7ec8" target="_blank" >
						 <img width="" style="max-height:65px;" alt="" border="0" src="<?=get_s3_url($site_logo)?>" class="CToWUd">
					</a>
					<?php
						}
						if($invoice_webshop_name_value=='yes'){

					?>
					<h2 style="text-align:center; font-size:18px;margin-bottom:0px;line-height:15px"><?php  echo ((isset($shop_name) && $shop_name != null) ? $shop_name : '' ); ?></h2>
					<?php } ?>
				</td>
			</tr>
		<?php } ?>
			<!-- end comment -->

			<tr>
				<td style="width:680px">
					<table border="0" cellpadding="0" cellspacing="0" style="width:680px">
					<tbody>
						<tr>
							<td style="width:100%;vertical-align: top;padding-bottom:5px;">
							<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;"><strong><?php  echo ((isset($shop_company_name) && $shop_company_name != null) ? $shop_company_name : '' ); ?></strong></p>
							<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px"><?php  echo ((isset($shop_bill_address_line1) && $shop_bill_address_line1 != null) ? $shop_bill_address_line1 : '' ); ?> <?php  echo ((isset($shop_bill_address_line2) && $shop_bill_address_line2 != null) ? $shop_bill_address_line2 : '' ); ?><br>
								<?php  echo ((isset($shop_bill_city) && $shop_bill_city != null) ? $shop_bill_city.',' : '' ); ?>
								<?php  echo ((isset($shop_bill_state) && $shop_bill_state != null) ? $shop_bill_state : '' ); ?>
								<?php  echo ((isset($shop_bill_pincode) && $shop_bill_pincode != null) ? $shop_bill_pincode : '' ); ?>
							</p>
							<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0; margin-top:5px;"><?php  echo ((isset($shop_user_email) && $shop_user_email != null) ? $shop_user_email : '' ); ?></p>
							<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px;">GSTIN: <?php  echo ((isset($shop_gst_no) && $user_shop_details->gst_no != null) ? $shop_gst_no : '' ); ?></p>
							<?php if(isset($invoice_add_field1_name)){ ?>
							<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px;"><?php  echo ((isset($invoice_add_field1_name) && $invoice_add_field1_name != null) ? $invoice_add_field1_name.':' : '' ); ?> <?php  echo ((isset($invoice_add_field1_value) && $invoice_add_field1_value != null) ? $invoice_add_field1_value : '' ); ?></p>
							<?php }
								if(isset($invoice_add_field2_name)){
							?>
							<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px;"><?php  echo ((isset($invoice_add_field2_name) && $invoice_add_field2_name != null) ? $invoice_add_field2_name.':' : '' ); ?> <?php  echo ((isset($invoice_add_field2_value) && $invoice_add_field2_value != null) ? $invoice_add_field2_value : '' ); ?></p>
							<?php } ?>

							<p style="color: #949494; font-size:18px; font-weight: 400; font-family: helvetica; line-height: 18px; margin-top: 15px; margin-bottom: 0;">Tax Invoice</p>
							</td>
						</tr>

						<tr>

						<td style="width:150px;vertical-align: top;padding-bottom:5px; padding-right:15px;">
								<p style="font-family:helvetica;font-size:14px; line-height:18px;margin-bottom:0;"><strong> BILL TO</strong></p>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px">
								<?=$bill_customer_name?>
								<!-- <?=$b2borderData->customer_name?> -->
								</p>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0; margin-top:5px;">
									<?=$invoice_shop_webshop_name?> <br><?=$invoice_shop_company_name?><br>
									<?=$invoice_billing_address_line1?>,<?=$invoice_billing_address_line2?><br>
									<?=$invoice_billing_city?>, <?=$invoice_billing_state?> <?=$invoice_billing_pincode?> <?=$invoice_billing_country?>

								<!-- <?=$b2borderData->org_shop_name?><br><?=$b2borderData->company_name?><br>
								<?=$b2borderData->bill_address_line1?>, <?=$b2borderData->bill_address_line2?><br><?=$b2borderData->bill_city?>, <?=$b2borderData->bill_state?> <?=$b2borderData->bill_pincode?> <?=$b2borderData->bill_country?> -->
								</p>

								<?php
									$bilto_state_code=$this->CommonModel->get_states_id($invoice_billing_state);
									//print_r($bilto_state_code->state_code);exit();
									// $bilto_state_code=$this->CommonModel->get_states_id($b2borderData->bill_state);
								?>

								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px;">State Code: <?php echo ((isset($bilto_state_code) && $bilto_state_code != null) ? $bilto_state_code->state_code : '' ); ?></p>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px;">GSTIN: <?=$invoice_shop_gst_no?><!-- <?=$b2borderData->gst_no?> --></p>
								<p style="font-family:helvetica;font-weight:normal; font-size:16px; line-height:18px;margin-bottom:0;"><strong> PLACE OF SUPPLY</strong></p>
								<?php
									//echo $invoice_ship_state;exit();
									$shipto_state_code1='';
									$shipto_state_code='';
									if(isset($invoice_ship_state)){
										$shipto_state_code=$this->CommonModel->get_states_id($invoice_ship_state);
										if($shipto_state_code){
											$shipto_state_code1=$shipto_state_code->state_code;
										}
									}

									// $shipto_state_code=$this->CommonModel->get_states_id($b2borderData->ship_state);
								?>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px;"><?php echo ((isset($shipto_state_code) && $shipto_state_code != null) ? $shipto_state_code->state_code : '' ); ?> - <?=$invoice_ship_state?></p>
						</td>

						<td style="width:150px;vertical-align: top;padding-bottom:5px; padding-right:15px;">
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;"><strong> SHIP TO</strong></p>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px">
								<!-- <?=$b2borderData->customer_name?> -->
								<?=$bill_customer_name?>
								</p>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0; margin-top:5px;">
									<?=$invoice_shop_webshop_name?> <br><?=$invoice_shop_company_name?><br>
									<?=$invoice_ship_address_line1?>,<?=$invoice_ship_address_line2?><br>
									<?=$invoice_ship_city?>, <?=$invoice_ship_state?> <?=$invoice_ship_pincode?> <?=$invoice_ship_country?>
								</p>

								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px;">State Code: <?php echo ((isset($shipto_state_code) && $shipto_state_code != null) ? $shipto_state_code->state_code : '' ); ?></p>

						</td>

						<td style="width:150px;vertical-align: top;padding-bottom:5px;">
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;"><span style=" width: 110px; display: inline-table;text-align: right;"><strong>INVOICE NO.</strong></span> <?php  echo ((isset($invoice_no) && $invoice_no != null) ? $invoice_no : '' ); ?></p>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;"><span style=" width: 110px; display: inline-table;text-align: right;"><strong>DATE</strong></span> <?php  echo ((isset($invoice_invoice_date) && $invoice_invoice_date != null) ? $invoice_invoice_date : '' ); ?></p>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;"><span style=" width: 110px; display: inline-table;text-align: right;"><strong>DUE DATE</strong></span> <?php  echo ((isset($invoice_invoice_due_date) && $invoice_invoice_due_date != null) ? $invoice_invoice_due_date : '' ); ?></p>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;"><span style=" width: 110px; display: inline-table;text-align: right;"><strong>TERMS</strong></span>
									<?php if($invoice_invoice_term > 0){echo $invoice_invoice_term.' Days';}else{echo "Due on receipt";} ?></p>

								<?php //echo date('d-m-Y',strtotime(time));?>
						</td>

						</tr>
					</tbody>
				</table>
			</td>
		</tr>



	<tr>
		<td>
			<table width="680" cellpadding="0" cellspacing="0" border="0" style="width:680px;padding:0px 0px 10px;margin:0;">
				<thead>
					<tr>
						<th width="10" style="font-family:helvetica;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:left;font-size:11px">
							NO</span>
						</th>
						<th width="20" style="font-family:helvetica;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">
							Order NO</span>
						</th>
						<th width="30" style="font-family:helvetica;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">
							HSN/SAC</span>
						</th>
						<th width="90" style="font-family:helvetica;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:left;font-size:11px" >
							PRODUCT</span>
						</th>
						<th width="60" style="font-family:helvetica;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">
							SKU
						</th>
						<th style="font-family:helvetica;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">
							TAX
						</th>
						<th  style="font-family:helvetica;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">
						 QTY
						</th>
						<th style="font-family:helvetica;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">
						 RATE
						</th>
						<th style="font-family:helvetica;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:right;font-size:11px">
						 AMOUNT
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
						if(isset($invoice_invoice_order_nos)){
							$order_ids = $invoicedata->invoice_order_nos;
						    $invoice_order_id = explode(",", $order_ids);
						    //$invoice_order_id=array('0' =>58, '1' =>60 );


						    //print_r($invoice_order_id);
						    // echo count($invoice_order_id);
						    /*if(count($invoice_order_id) > 0){
						    	$b2borderData=$this->B2BOrdersModel->get_pdf_b2border_invoicing_data($invoice_order_id);

						    }*/
						}
						//print_r($invoice_order_id);
						$b2borderData_item=$this->B2BOrdersModel->getOrder_multi_Items($invoice_order_id);
						// print_r($b2borderData_item);
						// exit();


						if(isset($b2borderData_item)){
							$i=1;
							// tax arrray generate
							$taxArray=array();
							$sumArray=array();
							$ItemRowTotal_Sum=0;

							$total_price_sum=0;
							$total_price_tax_sum=0;
							$final_total_price_tax=0;

							foreach ($b2borderData_item as $Items) {
								//print_r($Items);exit();
								$item_order_increment_id=$Items->increment_id;
								$order_id=$Items->order_id;
								$order_create_date=$Items->created_at;// create date
								$product_name=$Items->product_name;// create date
								$product_id=$Items->product_id;// product id
								// $product_hsn_code='';// product hsn code
								/*$product_barcode=$Items->product_id;// product id
								$product_barcode=$Items->barcode;// product barcode*/
								$product_barcode=$Items->barcode;// product barcode
								$product_variants=$Items->product_variants;// product barcode
								$product_type=$Items->product_type;// product barcode
								// print_r($product_id);
								if($Items->parent_product_id != 0 ){

									$product_main_id = $Items->parent_product_id;

								}else{

									$product_main_id = $Items->product_id;

								}
								$product_category=$this->CommonModel->getProductsMaintCategoryNames($product_main_id);// product catego


								// print_r($product_category);exit();
								// $product_category='';// product barcode
								$product_sku=$Items->sku;// product sku
								$product_qty=$Items->qty_ordered;// product barcode
								//$product_price=$Items->qty_ordered;// product barcode

								// start hsn code
								$product_hsn_code='';
								$parent_product_id=$Items->parent_product_id;// product id
								if($hsnMainId){
									//echo 'no';exit();
									// if($Items->product_type=='conf-simple'){
									if($product_type=='conf-simple'){
										$FinalproductID = $parent_product_id;
									}else{
										$FinalproductID = $product_id;
									}
									/*if($product_category=='conf-simple'){
										$FinalproductID = $product_id;
									}else{
										$FinalproductID = $parent_product_id;
									}*/
									// hsn code
									$shopProductAttributes=$this->B2BOrdersModel->getSingleDataByID('products_attributes',array('product_id'=>$FinalproductID,'attr_id'=>$hsnMainId),'*');
									//$shopProductAttributes=$this->B2BOrdersModel->getSingleDataByID('products_attributes',array('product_id'=>21,'attr_id'=>$hsnMainId),'*');
									if($shopProductAttributes){
										$product_hsn_code=$shopProductAttributes->attr_value;

									}
									//exit();

								}

								// $product_id=$Items->item_id;// create date
								$order_discount_percent=$Items->order_discount_percent; //b2b order percentage
								//order item data
								//$Itemsprice ='10000';
								if($Items->price > 0.00 && $order_discount_percent > 0.00) {
									$pro_price_excl_tax = $Items->price - ($Items->price * $order_discount_percent)/100; //90
									// $pro_price_excl_tax = $Items->price - ($Items->price * $order_discount_percent)/100; //90
								} else {
									$pro_price_excl_tax = $Items->price;
								}

								//echo $pro_price_excl_tax;exit();

								$ItemQty = $Items->qty_ordered;
								$ItemPrice = $pro_price_excl_tax;
								$ItemTaxPercent= $Items->tax_percent;
								$ItemRowTotal1= ($pro_price_excl_tax  * $ItemQty);
								$ItemRowTotal=$ItemRowTotal1;
								$ItemTaxAmount=0;
								if($ItemTaxPercent > 0 && $pro_price_excl_tax > 0){
									$ItemTaxAmount=($pro_price_excl_tax * $ItemTaxPercent) / 100;
									$ItemRowTaxAmount= ($ItemTaxAmount  * $ItemQty);
								}else{
									$ItemRowTaxAmount=0;
								}
								$ItemRowTotal_Sum += $ItemRowTotal;

								$gst_row_amount=$ItemRowTaxAmount;

								$order_shipment_type=$Items->order_shipment_type; //b2b order shipment Type 1-Buy In(directshop), 2-Dropship(othershopcustomer)
								//$tax_exampted=2;
								$total_amount_including_gst=$gst_row_amount + $ItemRowTotal;

								// if($order_shipment_type==1){
									if($tax_exampted==2){

										// array pass percentage and amount
										if(!array_key_exists($ItemTaxPercent, $taxArray)){
											$taxArray[$ItemTaxPercent]=array();
											$sumArray[$ItemTaxPercent]=array('final_tax_amount'=>0,'final_price'=>0);

										}

										$sumArray[$ItemTaxPercent]['final_tax_amount']=$sumArray[$ItemTaxPercent]['final_tax_amount'] + $ItemRowTaxAmount;
										$sumArray[$ItemTaxPercent]['final_price']=$sumArray[$ItemTaxPercent]['final_price'] + $ItemRowTotal;

									}else{

									}
								/*}else{

								}*/


								//array_push($taxArray[$ItemTaxPercent], array('final_tax_amount' => $ItemRowTaxAmount,'final_price' => $ItemRowTotal));
								/*array_push(
															'final_tax_amount' => $ItemRowTaxAmount,
							                      			'final_price' => $ItemRowTotal
							                      		);*/




								// buyin products check only
								/*if($Items->price > 0.00 && $b2borderData->discount_percent > 0.00) {
									$pro_price_excl_tax = $Items->price - ($Items->price * $b2borderData->discount_percent)/100; //90
								} else {
									$pro_price_excl_tax = $Items->price;
								}*/

								//print_r($b2borderData_item);
								// $Items->product_variants;
								// $total_price_sum+=$Items->total_price;
								$variant_data='';
								if(isset($Items->product_variants) && $Items->product_variants!=''){
									$variants=json_decode($Items->product_variants, true);
									if(isset($variants) && count($variants)>0){
										foreach($variants as $pk=>$single_variant){
											if($pk > 0){ $variant_data.= ', ';}
											foreach($single_variant as $key=>$val){
												//$variant_data.='-'.$val;
												$variant_data.=' '.$key.' - '.$val;
											}
										}
									}
								}
								if($variant_data){ $variant_data='('.$variant_data.')';}
					?>
					<tr>
						<td style="font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:5px 10px;margin:0;border-top:1px solid #ebebeb;text-align:center">
							<p style="font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;color:#787878; font-size: 12px;"><?=$i?></p>
						</td>
						<td style="text-align:center;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb; font-size: 12px; "><?=$item_order_increment_id?></td>
						<td style="text-align:center;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb; font-size: 12px; "><?=$product_hsn_code?></td>

						<td style="text-align:left;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb" >
							<span style="font-family:Helvetica Neue,helvetica,sans-serif; font-size: 12px;"><?=$Items->product_name?> <?=$variant_data?></span>
						</td>
						<td style="text-align:center;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb; ">
							<span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;"><?=$Items->sku?></span>
						</td>
						<td style="text-align:center;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb">
							<span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;"><?=$ItemTaxPercent?>%<br>GST</span>
						</td>

						<td style="text-align:right;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb; text-align:center;">
							<span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;"><?=$ItemQty?></span>
						</td>
						<td style="text-align:right;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb;  text-align:center;">
							<span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;"><?=number_format($ItemPrice,2)?></span>
						</td>
						<td style="text-align:right;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb">
							<span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;"><?=number_format($ItemRowTotal,2)?></span>
						</td>

					</tr>
					<?php
						$i++;
							$fbc_user_id=$this->session->userdata('LoginID');
							// invoiceing details data insert
							$insertinvoicingdetailsdata=array(
									'invoice_id'=>$invoice_id,
									'order_id'=>$order_id,
									'order_date'=>$order_create_date,
									'product_name'=>$product_name,
									'product_id'=>$product_id,
									'product_hsn_code'=>$product_hsn_code,
									'product_barcode'=>$product_barcode,
									'product_variants'=>$product_variants,
									'product_category'=>$product_category,
									'product_sku'=>$product_sku,
									'product_qty'=>$product_qty,
									'product_price'=>$pro_price_excl_tax,
									'place_of_supply'=>$shipto_state_code1,
									'gst_rates_applicable'=>$ItemTaxPercent,// percentage
									'gst_amount'=>$ItemTaxAmount, //item tax
									'gst_row_amount'=>$gst_row_amount,// item tax * qty
									//'total_row_amount'=>$total_row_amount,//
									'total_amount_excluding_gst'=>$ItemRowTotal,
									'total_amount_including_gst'=>$total_amount_including_gst, // gst_row_amount + total_amount_excluding_gst
									'created_by'=>$fbc_user_id,
									'created_at'=>time(),
									'ip'=>$_SERVER['REMOTE_ADDR']
							);
							//print_r($insertinvoicingdetailsdata);exit();
							if($invoice_id){
								$invoicing_detail=$this->B2BOrdersModel->insertData('invoicing_details',$insertinvoicingdetailsdata);
							}

								}
							}
					?>

				</tbody>
			</table>
		</td>
	</tr>
		<?php
			/*print_r($taxArray);
			echo "<pre>";
			print_r($sumArray);
			exit();*/

		?>
	<tr>
		<td>
			<table cellpadding="0" cellspacing="0" border="0" style="width:680px;padding:0;margin:0;border-top:1px dashed #c3ced4;">
			<tbody>
				<tr>

					<td style="font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:20px 0px;margin:0;text-align:left;line-height:20px" width="40%">
						<p style="font-family:helvetica;font-weight:normal;font-size:11px">Kindly make the payment online based on following:<br>
						BANK NAME : <?php  echo ((isset($user_shop_details->bank_name) && $user_shop_details->bank_name != null) ? $user_shop_details->bank_name : '' ); ?><br>
						A/C NAME : <?php  echo ((isset($user_shop_details->bank_acc_name) && $user_shop_details->bank_acc_name != null) ? $user_shop_details->bank_acc_name : '' ); ?><br>
						A/C NO : <?php  echo ((isset($user_shop_details->bank_acc_no) && $user_shop_details->bank_acc_no != null) ? $user_shop_details->bank_acc_no : '' ); ?><br>
						RTGS / NEFT IFSC : <?php  echo ((isset($user_shop_details->bank_ifsc) && $user_shop_details->bank_ifsc != null) ? $user_shop_details->bank_ifsc : '' ); ?><br>
						BIC/SWIFT : <?php  echo ((isset($user_shop_details->bic_swift) && $user_shop_details->bic_swift != null) ? $user_shop_details->bic_swift : '' ); ?><br>
						BRANCH : <?php  echo ((isset($user_shop_details->bank_branch) && $user_shop_details->bank_branch != null) ? $user_shop_details->bank_branch : '' ); ?><br>
						<!-- A/C NAME : Z-WEAR DISTRIBUTION INDIA PRIVATE LIMITED;<br>
						A/C NO : 50200048825869;<br>
						TYPE : CURRENT A/C;<br>
						RTGS / NEFT IFSC : HDFC0000291<br>
						BRANCH : EXPRESS TOWER, NARIMAN POINT, MUMBAI 400021</p> -->
					</td>
					<td style="font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:20px 0px;margin:0;text-align:right;line-height:20px; padding-left: 25px;" width="60%">
						<table cellpadding="0" cellspacing="0" border="0" style="width:100%;padding:0;margin:0">
							<tbody>
							<?php
								// subtotal all amount
								$subtotalsumamount=$ItemRowTotal_Sum ;

							?>
							<tr style="padding-bottom:5px">
								<td  align="right" style="padding:5px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;width: 60%;     text-align: left;">
									TOTAL EXCLUDING TAXES
								</td>
								<td align="right" style="padding:3px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;">
									<span style="font-family:Helvetica Neue,helvetica,sans-serif"><?=$currency.' '.number_format($subtotalsumamount,2)?> </span>
								</td>
							</tr>
							<?php
								$total_price_tax_sum=0;
								if(isset($sumArray)){
									// $taxType1='CGST';


									$taxType1='IGST';
									$taxType2='';
									if(isset($user_shop_details->bill_state) && isset($invoice_ship_state)){
										if($user_shop_details->bill_state==$invoice_ship_state){
											$taxType1='CGST';
											$taxType2='SGST';
										}
									}
									$finaltaxAmountSum=0;
									foreach ($sumArray as $sumArrayKey => $sumArrayValue) {

										$taxPercentage=$sumArrayKey;
										$final_TaxAmount=$sumArrayValue['final_tax_amount'];
										// $final_TaxAmount=number_format($sumArrayValue['final_tax_amount'],2);
										$finalPrice=$sumArrayValue['final_price'];
										// $finalPrice=number_format($sumArrayValue['final_price'],2);

										$finaltaxAmountSum += $final_TaxAmount;
									if($invoice_billing_country=='IN'){
										if($taxType1=='IGST'){
							?>
							<tr style="padding-bottom:5px">
								<td  align="right" style="padding:5px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;width: 60%;  text-align: left;">
									<?php echo $taxType1.' @ '.$taxPercentage.'% on '.number_format($finalPrice,2);?>
								</td>
								<td align="right" style="padding:5px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;">
									<span style="font-family:Helvetica Neue,helvetica,sans-serif"><?=number_format($final_TaxAmount,2)?></span>
								</td>

							</tr>


							<?php

								 }else{

									$actual=$taxPercentage/2;
									// $actualAmount=$finalPrice/2;
									$actualAmount=$finalPrice;
									$actual_percentage1= ($actual * $actualAmount) / 100 ;
									$actual_percentage= $actual_percentage1;
									// $actual_percentage= number_format($actual_percentage1,2);
									// final total
									$total_price_tax_sum += $actual_percentage + $actual_percentage;
									//$amount_deduct_tax1=$actualAmount - $actual_percentage;
									$amount_deduct_tax=$actualAmount;
									// $amount_deduct_tax=number_format($actualAmount,2);

							?>
							<tr style="padding-bottom:5px">
								<td  align="right" style="padding:5px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;width: 60%;  text-align: left;">
									<?php echo $taxType1.' @ '.$actual.'% on '.number_format($amount_deduct_tax,2);?>
								</td>
								<td align="right" style="padding:5px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;">
									<span style="font-family:Helvetica Neue,helvetica,sans-serif"><?=number_format($actual_percentage,2)?> </span>
								</td>

							</tr>
							<tr style="padding-bottom:5px">
								<td  align="right" style="padding:5px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;width: 60%;  text-align: left;">
									<?php echo $taxType2.' @ '.$actual.'% on '.number_format($amount_deduct_tax,2);?>
								</td>
								<td align="right" style="padding:5px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;">
									<span style="font-family:Helvetica Neue,helvetica,sans-serif"><?=number_format($actual_percentage,2)?></span>
								</td>

							</tr>

							<?php

										}
									  }else{
							?>
								<tr style="padding-bottom:5px">
									<td  align="right" style="padding:5px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;width: 60%;  text-align: left;">

										<?php echo $taxPercentage.'% on '.number_format($finalPrice,2);?>
									</td>
									<td align="right" style="padding:5px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;">
										<span style="font-family:Helvetica Neue,helvetica,sans-serif"><?=number_format($final_TaxAmount,2)?></span>
									</td>

								</tr>
							<?php

									  }

								  }

								}
								/*echo $finaltaxAmountSum;
								exit();*/

								// final total
								if($ItemRowTotal_Sum > 0 ){ $ItemRowTotal_Sum = $ItemRowTotal_Sum;}else{ $ItemRowTotal_Sum=0;}
								if($finaltaxAmountSum > 0 ){ $finaltaxAmountSum =$finaltaxAmountSum;}else{ $finaltaxAmountSum = 0;}

								//echo $ItemRowTotal_Sum;
								//exit();
								$final_total_price_tax = $ItemRowTotal_Sum + $finaltaxAmountSum;
							?>



								<tr style="padding-bottom:5px">
									<td  align="right" style="padding:5px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;width: 60%;     text-align: left;">
										TOTAL INCLUDING TAXES
									</td>
									<td align="right" style="padding:3px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;">
										<span style="font-family:Helvetica Neue,helvetica,sans-serif"><?=number_format($final_total_price_tax,2)?> </span>
									</td>
								</tr>

								<tr>
									<td  align="right" style="padding:3px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;width: 60%;     text-align: left;">
										<strong>BALANCE DUE</strong>
									</td>
									<td align="right" style="padding:3px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;">
										<strong><span style="font-family:Helvetica Neue,helvetica,sans-serif"><?=$currency.' '.number_format($final_total_price_tax,2)?></span></strong>
									</td>
								</tr>

								<tr>
									<td  colspan="2" align="left" style="padding:3px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0; text-align:left;font-size:14px;">
										<?php
											echo $this->CommonModel->getIndianCurrencytoText($final_total_price_tax);
										?>

									</td>
								</tr>
							</tbody>
						</table>
					</td>

					<?php
						// update invoice table
						/*$invoice_subtotal=number_format($ItemRowTotal_Sum,2);
						$invoice_tax=number_format($finaltaxAmountSum,2);*/

						$invoice_subtotal=$ItemRowTotal_Sum;
						$invoice_tax=$finaltaxAmountSum;

						$invoice_grand_total=$final_total_price_tax;
						$invoice_update=array('invoice_subtotal'=>$invoice_subtotal,'invoice_tax'=>$invoice_tax,'invoice_grand_total'=>$invoice_grand_total,'updated_at'=>time());
						$where_invoice_arr=array('id'=>$invoice_id);
						$invoioceUpdated=$this->B2BOrdersModel->updateData('invoicing',$where_invoice_arr,$invoice_update);
						//if($invoioceUpdated){ print_r($invoioceUpdated); exit();}
						//exit();

					?>
				</tr>
			</tbody>
			</table>
		</td>
	</tr>

	<?php if(!empty($invoice_bottom_message_value)){?>
	<tr>
		<td>
			<table cellspacing="2" style="width:100%;border-top:1px solid #ccc">
				<tr>
					<td style="height:45px;padding:10px 13px;text-align:center">
						<p><?=$invoice_bottom_message_value?></p>
					</td>

				</tr>
			</table>
		</td>
	</tr>
	<?php } ?>



</table>
      <!-- table 1 end -->
  </body>
</html>
