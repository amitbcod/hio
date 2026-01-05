<?php
	//balanced amount
	$balancedAmount=0;
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
	$customer_name= $invoice_customer_first_name.' '.$invoice_customer_last_name;
	$invoice_customer_id=$invoicedata->customer_id;
	$invoice_customer_email=$invoicedata->customer_email;
	$invoice_shop_id=$invoicedata->shop_id;
	// b2b tax_exampted
	$tax_exampted='';
	/*if($invoice_customer_id){

		$taxExampted=$this->CommonModel->getSingleShopDataByID('customers',array('id'=>$invoice_customer_id),'tax_exampted');
		if(isset($taxExampted->tax_exampted)){
			$tax_exampted=$taxExampted->tax_exampted;
		}else{
			$tax_exampted='';
		}
	}*/



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
	// new add
	$invoice_bill_customer_company_name=$invoicedata->bill_customer_company_name;
	$invoice_bill_customer_gst_no=$invoicedata->bill_customer_gst_no;
	// new end

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


	// new shipping_charges,payment_charges,voucher_amount
	$shipping_charges=$invoicedata->shipping_charges;
	$voucher_amount=$invoicedata->voucher_amount;
	$voucher_amount_used=0;
	$voucher_amount_remain=0;


	$payment_charges=$invoicedata->payment_charges;
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

	/*if($invoicedata->invoice_term > 0){
		$invoice_invoice_term=$invoicedata->invoice_term. ' Days';
	}else{
		$invoice_invoice_term=$invoice_invoice_date. ' Day';
	}*/
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

	/*start new changes*/
	$mobile_number_billing='';
	$mobile_number_shipping='';
	$b2b_order_increment_id='';
	$webshop_order_increment_id='';
    if(!empty($b2b_order_id)){
    	$b2b_order_data=$this->CommonModel->getSingleShopDataByID('b2b_orders',array('order_id' =>$b2b_order_id),'increment_id');
    	if(isset($b2b_order_data) && !empty($b2b_order_data->increment_id)){
    		$b2b_order_increment_id=$b2b_order_data->increment_id;
    	}
    }
	if(isset($invoice_invoice_order_nos)){
		$order_ids = $invoicedata->invoice_order_nos;
	    $invoice_order_id = explode(",", $order_ids);
	    //$invoice_order_id=array('0' =>58, '1' =>60 );
	    if(count($invoice_order_id) == 1){
	    	$webshop_order_data=$this->ShopProductModel->getSingleShopDataByID('sales_order',array('order_id' =>$order_ids),'increment_id');
	    	$webshop_order_increment_id=$webshop_order_data->increment_id;

	    	// mobile no

	    	$mobile_number_billing_data=$this->ShopProductModel->getSingleShopDataByID('sales_order_address',array('order_id' =>$order_ids, 'address_type' =>1),'mobile_no');
	    	$mobile_number_billing='<br/>Mobile No. '.$mobile_number_billing_data->mobile_no;
	    	$mobile_number_shipping_data=$this->ShopProductModel->getSingleShopDataByID('sales_order_address',array('order_id' =>$order_ids, 'address_type' =>2),'mobile_no');
	    	$mobile_number_shipping='<br/>Mobile No. '.$mobile_number_shipping_data->mobile_no;

	    }else{

	    }
	}

	/*end new changes*/

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
    <?php //print_r($b2borderData->company_name);?>
	<table style="width:680px;max-width:680px;background:#fff;font-size:14px;font-family:helvetica;color:#000000;margin-bottom:10px;" >
		<!-- comment -->
			<?php if(!empty($invoice_logo_value) || !empty($invoice_webshop_name_value)){?>
				<tr>
					<td valign="middle" align="center" style="padding:10px 0 0px 0">
						<?php //print_r($user_web_shop_details); //exit();
							if($invoice_logo_value=='yes'){
								$site_logo = $this->encryption->decrypt($user_web_shop_details['site_logo']);
						?>
						 <a href="" style="color:#000000" target="_blank" >
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
       <!-- comment -->

			<tr>
				<td style="width:671px">
					<table border="0" cellpadding="0" cellspacing="0" style="width:680px">
					<tbody>
						<tr>
							<td colspan="3" style="width:100%;vertical-align: top;padding-bottom:5px;">
							<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;"><strong><?php  echo ((isset($shop_company_name) && $shop_company_name != null) ? $shop_company_name : '' ); ?></strong></p>
							<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px"><?php  echo ((isset($shop_bill_address_line1) && $shop_bill_address_line1 != null) ? $shop_bill_address_line1 : '' ); ?> <?php  echo ((isset($shop_bill_address_line2) && $shop_bill_address_line2 != null) ? $shop_bill_address_line2 : '' ); ?><br>
								<?php  echo ((isset($shop_bill_city) && $shop_bill_city != null) ? $shop_bill_city.',' : '' ); ?>
								<?php  echo ((isset($shop_bill_state) && $shop_bill_state != null) ? $shop_bill_state : '' ); ?>
								<?php  echo ((isset($shop_bill_pincode) && $shop_bill_pincode != null) ? $shop_bill_pincode : '' ); ?>
							</p>
							<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px;">GSTIN: <?php  echo ((isset($shop_gst_no) && $user_shop_details->gst_no != null) ? $shop_gst_no : '' ); ?></p>
							<!-- <p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0; margin-top:5px;"><?php  echo ((isset($shop_user_email) && $shop_user_email != null) ? $shop_user_email : '' ); ?></p>
							<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px;">GSTIN: <?php  echo ((isset($shop_gst_no) && $user_shop_details->gst_no != null) ? $shop_gst_no : '' ); ?></p>
							<?php if(isset($invoice_add_field1_name)){ ?>
							<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px;"><?php  echo ((isset($invoice_add_field1_name) && $invoice_add_field1_name != null) ? $invoice_add_field1_name.':' : '' ); ?> <?php  echo ((isset($invoice_add_field1_value) && $invoice_add_field1_value != null) ? $invoice_add_field1_value : '' ); ?></p>
							<?php }
								if(isset($invoice_add_field2_name)){
							?>
							<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px;"><?php  echo ((isset($invoice_add_field2_name) && $invoice_add_field2_name != null) ? $invoice_add_field2_name.':' : '' ); ?> <?php  echo ((isset($invoice_add_field2_value) && $invoice_add_field2_value != null) ? $invoice_add_field2_value : '' ); ?></p>
							<?php } ?> -->

							<p style="color: #000000; font-size:18px; font-weight: 400; font-family: helvetica; line-height: 18px; margin-top: 15px; margin-bottom: 0;">Tax Invoice</p>
							</td>
						</tr>

						<tr>

						<td style="width:150px;vertical-align: top;padding-bottom:5px; padding-right:15px;">
								<p style="font-family:helvetica;font-size:14px; line-height:18px;margin-bottom:0;"><strong> BILL TO</strong></p>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px">
								<?=$bill_customer_name?>
								<?php
									// new added
								//$invoice_bill_customer_gst_no
										if($invoice_bill_customer_company_name){
											echo '<br/>'.$invoice_bill_customer_company_name;
										}
								?>
								<!-- <?=$b2borderData->customer_name?> -->
								</p>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0; margin-top:5px;">
									<!-- <?=$invoice_shop_webshop_name?> <br><?=$invoice_shop_company_name?><br> -->
									<?=$invoice_billing_address_line1?>,<?=$invoice_billing_address_line2?><br/>
									<?=$invoice_billing_city?>, <?=$invoice_billing_state?> <?=$invoice_billing_pincode?> <?=$invoice_billing_country?>
									<?=$mobile_number_billing?>

								<!-- <?=$b2borderData->org_shop_name?><br><?=$b2borderData->company_name?><br>
								<?=$b2borderData->bill_address_line1?>, <?=$b2borderData->bill_address_line2?><br><?=$b2borderData->bill_city?>, <?=$b2borderData->bill_state?> <?=$b2borderData->bill_pincode?> <?=$b2borderData->bill_country?> -->
								</p>

								<?php
									$bilto_state_code_data="";
									if($invoice_billing_state){
										if($this->CommonModel->get_states($invoice_billing_state)){
											$bilto_state_code_data=$this->CommonModel->get_states($invoice_billing_state);
											$bilto_state_code=$bilto_state_code_data;
										}
									}
									// $bilto_state_code=$this->CommonModel->get_states_id($b2borderData->bill_state);
								?>

								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px;">State Code: <?php echo ((isset($bilto_state_code) && $bilto_state_code != null) ? $bilto_state_code->state_code : '' ); ?>
									<?php
										// new added
										if($invoice_bill_customer_gst_no){
											echo '<br/>GSTIN :'.$invoice_bill_customer_gst_no;
										}
									?>
								</p>
								<!-- <p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px;">GSTIN: <?=$invoice_shop_gst_no?></p> -->
								<p style="font-family:helvetica;font-weight:normal; font-size:16px; line-height:18px;margin-bottom:0;"><strong> PLACE OF SUPPLY</strong></p>
								<?php
									//echo $invoice_ship_state;exit();
									/*if(isset($invoice_ship_state)){
										$shipto_state_code_data=$this->CommonModel->get_states_id($invoice_ship_state);
										if(isset($shipto_state_code_data)){$shipto_state_code_data=$shipto_state_code_data;}else{
											$shipto_state_code_data='';
										}
									}else{
										$shipto_state_code='';
									}*/

									$shipto_state_code="";
									$shipto_state_code1="";
									if($invoice_ship_state){
										if($this->CommonModel->get_states($invoice_ship_state)){
											$shipto_state_code_data=$this->CommonModel->get_states($invoice_ship_state);
											$shipto_state_code=$shipto_state_code_data;
											if($shipto_state_code){
												$shipto_state_code1=$shipto_state_code->state_code; //add all
											}

										}
									}

									// $shipto_state_code=$this->CommonModel->get_states_id($b2borderData->ship_state);
								?>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px;"><?php echo ((isset($shipto_state_code) && $shipto_state_code != null) ? $shipto_state_code->state_code.'-' : '' ); ?> <?=$invoice_ship_state?></p>
						</td>

						<td style="width:150px;vertical-align: top;padding-bottom:5px; padding-right:15px;">
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;"><strong> SHIP TO</strong></p>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px">
								<!-- <?=$b2borderData->customer_name?> -->
								<?=$customer_name?>
								</p>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0; margin-top:5px;">
									<!-- <?=$invoice_shop_webshop_name?> <br><?=$invoice_shop_company_name?><br> -->
									<?=$invoice_ship_address_line1?>,<?=$invoice_ship_address_line2?><br>
									<?=$invoice_ship_city?>, <?=$invoice_ship_state?> <?=$invoice_ship_pincode?> <?=$invoice_ship_country?>
									<?=$mobile_number_shipping?>
								</p>

								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;margin-top:5px;">State Code: <?php echo ((isset($shipto_state_code) && $shipto_state_code != null) ? $shipto_state_code->state_code : '' ); ?></p>

						</td>

						<td style="width:150px;vertical-align: top;padding-bottom:5px;">
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;"><span style=" width: 110px; display: inline-table;text-align: right;"><strong>INVOICE NO.</strong></span> <?php  echo ((isset($invoice_no) && $invoice_no != null) ? $invoice_no : '' ); ?></p>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;"><span style=" width: 110px; display: inline-table;text-align: right;"><strong>DATE</strong></span> <?php  echo ((isset($invoice_invoice_date) && $invoice_invoice_date != null) ? $invoice_invoice_date : '' ); ?></p>
								<?php if(isset($webshop_order_increment_id) && !empty($webshop_order_increment_id)){ ?>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;"><span style=" width: 110px; display: inline-table;text-align: right;"><strong>ORDER NO.</strong></span> <?php  echo ((isset($webshop_order_increment_id) && $webshop_order_increment_id != null) ? $webshop_order_increment_id : '' ); ?></p>

								<?php }
									 if(isset($b2b_order_increment_id) && !empty($b2b_order_increment_id)){
								?>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;"><span style=" width: 110px; display: inline-table;text-align: right;"><strong>B2B ORDER NO.</strong></span>
									<?php if(!empty($b2b_order_increment_id)){echo $b2b_order_increment_id;}?></p>
								<?php } ?>
								<!-- <p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;"><span style=" width: 110px; display: inline-table;text-align: right;"><strong>DUE DATE</strong></span> <?php  echo ((isset($invoice_invoice_due_date) && $invoice_invoice_due_date != null) ? $invoice_invoice_due_date : '' ); ?></p>
								<p style="font-family:helvetica;font-weight:normal; font-size:14px; line-height:18px;margin-bottom:0;"><span style=" width: 110px; display: inline-table;text-align: right;"><strong>TERMS</strong></span>
									<?php if($invoice_invoice_term > 0){echo $invoice_invoice_term.' Days';}else{echo "Due on receipt";} ?></p> -->

								<?php //echo date('d-m-Y',strtotime(time));?>
						</td>

						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</table>
	
	<table width="680" cellpadding="0" cellspacing="0" border="0" style="width:680px;padding:0px 0px 10px;margin:0;">
		<thead>
			<tr>
				<th width="10" style="font-family:helvetica;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:left;font-size:11px">
					NO</span>                                                    
				</th>
				<!-- <th width="20" style="font-family:helvetica;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">
					Order NO</span>                                                    
				</th> -->
				<th width="30" style="font-family:helvetica;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">
					HSN/SAC</span>                                                    
				</th>
				<th width="110" style="font-family:helvetica;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:left;font-size:11px" >
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
				/*if(isset($invoice_invoice_order_nos)){
					$order_ids = $invoicedata->invoice_order_nos;
				    $invoice_order_id = explode(",", $order_ids);
				}*/
				//print_r($invoice_order_id);
				// $webshoporderData_item=$this->ShopProductModel->getOrder_multi_Items($invoice_order_id);
				$inv_count=$this->ShopProductModel->get_invoice_count_by_id($invoice_order_id,1);



				if($voucher_amount > 0.00){
					// sum used voucher amount
					$voucher_used=$this->ShopProductModel->get_invoice_sum_voucher_amount_by_id($invoice_order_id,1);
					//print_r($voucher_used[0]->total_used_voucher_amount);
					if(isset($voucher_used) && $voucher_used[0]->total_used_voucher_amount > 0.00){
						$voucher_amount=$voucher_amount - $voucher_used[0]->total_used_voucher_amount;
					}
				}

				//exit();

				 // print_r(count($inv_count));exit();
				$webshoporderData_item=$this->ShopProductModel->getOrder_multi_Items_new($invoice_order_id);
				//print_r($webshoporderData_item);exit();
				if(isset($webshoporderData_item)){ 
					// check b2b to webshop
					$skuArray=array();
					$qtyOrderArray=array();
					if(isset($parent_id) && $parent_id>0){
						//$b2b_order_id
						if($b2b_order_id){
							$b2bOrderData=$this->B2BOrdersModel->getOrder_multi_Items($b2b_order_id);
							foreach ($b2bOrderData as $b2bkey => $b2bvalue) {
								// sku qty_ordered
								//print_r($b2bvalue);
								//array_push($b2bsplitarray,array('sku' =>$b2bvalue->sku ,'qty_ordered' =>$b2bvalue->qty_ordered ));
								//$b2bsplitarray[]=array('sku' =>$b2bvalue->sku ,'qty_ordered' =>$b2bvalue->qty_ordered );
								$skuArray[]=$b2bvalue->sku;
								// $qtyOrderArray[$b2bvalue->sku]=$b2bvalue->qty_ordered;// old
								$qtyOrderArray[$b2bvalue->sku]=$b2bvalue->qty_scanned;
								//$b2bsplitarray[$b2bvalue->sku]=$b2bvalue->qty_ordered;
								/*$b2bsplitarray['sku']=$b2bvalue->sku; 
								$b2bsplitarray['qty_ordered']=$b2bvalue->qty_ordered;*/
							}

						}

					}else{
						if($b2b_order_id){
							$b2bOrderData=$this->B2BOrdersModel->getOrder_multi_Items($b2b_order_id);
							foreach ($b2bOrderData as $b2bkey => $b2bvalue) {
								$skuArray[]=$b2bvalue->sku;
								$qtyOrderArray[$b2bvalue->sku]=$b2bvalue->qty_scanned;
								
							}

						}
					}


						
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
					$final_total_price_tax=0;
					$orderItemQty=0; // total order item
					foreach ($webshoporderData_item as $Items) {
						//print_r($Items->sku);
						if(in_array($Items->sku, $skuArray)){
						
							$b2b_order_dat_qty=$qtyOrderArray[$Items->sku];

						//exit();
						// }

						//exit();

						//payment method
						$payment_method=$Items->payment_method; // user type login guest
						$payment_name=$Items->payment_method_name; // user type login guest
						
						$checkout_method=$Items->checkout_method; // user type login guest
						$invoice_self=$Items->invoice_self; // invoice send 1-yes 0-No
						$item_order_increment_id=$Items->increment_id;
						$order_id=$Items->order_id;
						$order_coupon_code=$Items->order_coupon_code;
						$order_create_date=$Items->created_at;// create date
						$product_name=$Items->product_name;// create date
						$product_id=$Items->product_id;// product id
						$product_barcode=$Items->barcode;// product barcode
						$product_variants=$Items->product_variants;// product barcode
						$product_sku=$Items->sku;// product sku
						// $product_qty=$Items->qty_ordered;// old
						$product_qty=$b2b_order_dat_qty;// product
						$product_type=$Items->product_type;
						if($Items->parent_product_id != 0 ){

							$product_main_id = $Items->parent_product_id;

						}else{

							$product_main_id = $Items->product_id;

						}
						$product_category=$this->ShopProductModel->getProductsMaintCategoryNames($product_main_id);// product catego

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
							
							// hsn code
							$shopProductAttributes=$this->ShopProductModel->getSingleShopDataByID('products_attributes',array('product_id'=>$FinalproductID,'attr_id'=>$hsnMainId),'*');
							//print_r($shopProductAttributes);exit();
							if($shopProductAttributes){
								$product_hsn_code=$shopProductAttributes->attr_value;

							}


						}

						// end hsn code

						// new price
						$coupon_code=$order_coupon_code;
						$price=$Items->price;
						$order_discount_percent=$Items->discount_percent; //b2b order percentage
						// $order_discount_percent=$Items->order_discount_percent; //old

						if($coupon_code != "" && $price > 0.00 && $order_discount_percent > 0.00) {
							$pro_price_incl_tax = $price - ($price*$order_discount_percent)/100;						
						} else {
							$pro_price_incl_tax = $price;
						}

						// $ItemQty = $Items->qty_ordered;//old
						$ItemQty = $b2b_order_dat_qty;
						// $orderItemQty +=$ItemQty; //old
						$orderItemQty =$Items->total_qty_ordered; //
						$ItemTaxAmount=0;
						$ItemRowTaxAmount=0;
						$ItemRowTotal = 0;
						$ItemTaxPercent= $Items->tax_percent;

						if($ItemTaxPercent > 0.00 && $pro_price_incl_tax > 0.00) {
							$pro_price_excl_tax = $pro_price_incl_tax / ((100+$ItemTaxPercent)/100);
							$ItemTaxAmount =  $pro_price_incl_tax - $pro_price_excl_tax;
							$ItemRowTaxAmount = ($ItemTaxAmount*$ItemQty);			
							// $ItemRowTaxAmount = number_format($ItemTaxAmount*$ItemQty,2);		
						} else{						
							$pro_price_excl_tax = $pro_price_incl_tax;
							$ItemTaxPercent = 0;					
							
						}

						$ItemPrice =$pro_price_excl_tax;
						$ItemRowTotal= $pro_price_excl_tax  * $ItemQty;
						
						// end new price


						$ItemRowTotal_Sum += $ItemRowTotal;
						
						$gst_row_amount=$ItemRowTaxAmount;

						$order_shipment_type=$Items->order_shipment_type; //b2b order shipment Type 1-Buy In(directshop), 2-Dropship(othershopcustomer)
						//$tax_exampted=2;
						$total_amount_including_gst=$gst_row_amount + $ItemRowTotal;

						//if($order_shipment_type==1){
							// if($tax_exampted==2){

								// array pass percentage and amount
								if(!array_key_exists($ItemTaxPercent, $taxArray)){
									$taxArray[$ItemTaxPercent]=array();
									$sumArray[$ItemTaxPercent]=array('final_tax_amount'=>0,'final_price'=>0);

								}		

								$sumArray[$ItemTaxPercent]['final_tax_amount']=$sumArray[$ItemTaxPercent]['final_tax_amount'] + $ItemRowTaxAmount;
								$sumArray[$ItemTaxPercent]['final_price']=$sumArray[$ItemTaxPercent]['final_price'] + $ItemRowTotal;

							/*}else{

							}*/
						/*}else{

						}*/

						
						//array_push($taxArray[$ItemTaxPercent], array('final_tax_amount' => $ItemRowTaxAmount,'final_price' => $ItemRowTotal));	
						/*array_push(
													'final_tax_amount' => $ItemRowTaxAmount,
					                      			'final_price' => $ItemRowTotal
					                      		);*/
						
						



						//print_r($webshoporderData_item);
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
						

						//exit();
			?>
			<tr>
				<td style="font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:5px 10px;margin:0;border-top:1px solid #ebebeb;text-align:center">
					<p style="font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;color:#000000; font-size: 12px;"><?=$i?></p>
				</td>
				<!-- <td style="text-align:center;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb; font-size: 12px; "><?=$item_order_increment_id?></td> -->
				<td style="text-align:center;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb; font-size: 12px; "><?=$product_hsn_code?></td>
				
				<td style="text-align:left;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb">
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

						

						$invoicing_detail=$this->ShopProductModel->insertData('invoicing_details',$insertinvoicingdetailsdata);
						
				   }// array check array_in end
				}  
				
				if(isset($inv_count) && count($inv_count)==1){
					$shippingorderItemQty=1;
					//print_r($orderItemQty);exit();
					if($shipping_charges > 0.00){ 
				//if($shipping_charges > 0.00){
					
					$shipping_charge=$Items->shipping_charge; // actual amount
					$shipping_tax_percent=$Items->shipping_tax_percent; //tax percentage
					$shipping_tax_amount=$Items->shipping_tax_amount; //after calculation only tax amount
					$shipping_amount=$Items->shipping_amount; // final amount including tax
					//print_r($webshoporderData_item);exit();
					// $shippingItemRowTotal=$shipping_charge * $orderItemQty;//old
					$shippingItemRowTotal=$shipping_charge;
					// $shippingItemRowTotal=$shipping_charge * $ItemQty; old
				//}
					//
					if($shipping_tax_percent > 0.00 && $shipping_charge > 0.00) {
						$shipping_charge_RowExcTax = ($shipping_charge);
						// $shipping_charge_RowExcTax = ($shipping_charge*$orderItemQty);//old
						// $shipping_charge_RowExcTax = ($shipping_charge*$ItemQty);//old 
						$shipping_charge_tax_amount = ($shipping_tax_percent / 100) * $shipping_charge;
						// $shipping_charge_RowTaxAmount = ($shipping_charge_tax_amount*$orderItemQty);old
						$shipping_charge_RowTaxAmount = $shipping_charge_tax_amount;
						// $shipping_charge_RowTaxAmount = ($shipping_charge_tax_amount*$ItemQty);//old
						$shipping_ItemRowTotal = $shipping_charge + $shipping_charge_RowTaxAmount;
					}else{						
						$shipping_charge_RowExcTax=0;			
						$shipping_ItemRowTotal=0;			
						$shipping_charge_tax_amount=0;
						$shipping_charge_RowTaxAmount =0;
					}
					// array pass percentage and amount
					if(!array_key_exists($shipping_tax_percent, $taxArray)){
						$taxArray[$shipping_tax_percent]=array();
						$sumArray[$shipping_tax_percent]=array('final_tax_amount'=>0,'final_price'=>0);

					}		

					$sumArray[$shipping_tax_percent]['final_tax_amount']=$sumArray[$shipping_tax_percent]['final_tax_amount'] + $shipping_charge_RowTaxAmount;
					$sumArray[$shipping_tax_percent]['final_price']=$sumArray[$shipping_tax_percent]['final_price'] + $shipping_charge;

					// $shippingRowTotal_Sum +=$shipping_charge; //actual charge witout tax
					$shippingRowTotal_Sum +=$shipping_ItemRowTotal; //include tax
					$shippingRowTotal_Sum_ex_tax +=$shipping_charge_RowExcTax; //exclude tax 
					$shippingRowTotal_tax +=$shipping_charge_RowTaxAmount; //actual charge witout tax
					//$codRowTotal_Sum=0;$codRowTotal_Sum $shippingRowTotal_Sum

					// insert data invoicing details 
					$fbc_user_id=$this->session->userdata('LoginID');
					// invoiceing details data insert
					$insertinvoicingdetailsdata_shipping=array(	
							'invoice_id'=>$invoice_id,
							'order_id'=>$order_id,
							'order_date'=>$order_create_date,
							'product_name'=>'Shipping Charges',
							// 'product_id'=>$product_id,
							// 'product_hsn_code'=>$product_hsn_code,
							// 'product_barcode'=>$product_barcode,
							// 'product_variants'=>$product_variants,
							// 'product_category'=>$product_category,
							'product_sku'=>'Ship',
							'product_qty'=>$shippingorderItemQty,
							'product_price'=>$shipping_charge,
							'place_of_supply'=>$shipto_state_code1,
							'gst_rates_applicable'=>$shipping_tax_percent,// percentage
							'gst_amount'=>$shipping_charge_tax_amount, //item tax
							'gst_row_amount'=>$shipping_charge_RowTaxAmount,// item tax * qty
							//'total_row_amount'=>$total_row_amount,//
							'total_amount_excluding_gst'=>$shipping_charge_RowExcTax,
							'total_amount_including_gst'=>$shipping_ItemRowTotal, // gst_row_amount + total_amount_excluding_gst
							'created_by'=>$fbc_user_id,
							'created_at'=>time(),
							'ip'=>$_SERVER['REMOTE_ADDR']
					);
					$this->ShopProductModel->insertData('invoicing_details',$insertinvoicingdetailsdata_shipping);

					

			?>
			<tr>
				<td style="font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:5px 10px;margin:0;border-top:1px solid #ebebeb;text-align:center">
					<p style="font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;color:#000000; font-size: 12px;"><?=$i?></p>
				</td>
				<!-- <td style="text-align:center;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb; font-size: 12px; "><?=$item_order_increment_id?></td> -->
				<td style="text-align:center;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb; font-size: 12px; "><!-- <?=$product_hsn_code?> --></td>
				
				<td style="text-align:left;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb">
					<span style="font-family:Helvetica Neue,helvetica,sans-serif; font-size: 12px;">Shipping Charges</span>                    
				</td>
				<td style="text-align:center;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb; ">
					<span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;">Ship</span>                    
				</td>
				<td style="text-align:center;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb">
					<span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;"><?=$shipping_tax_percent?>%</span>                    
				</td>

				<td style="text-align:right;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb; text-align:center;">
					<span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;"><?=$shippingorderItemQty?></span>                    
					<!-- <span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;"><?=$ItemQty?></span>                     -->
				</td>
				<td style="text-align:right;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb;  text-align:center;">
					<span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;"><?=number_format($shipping_charge,2)?></span>                    
				</td>
				<td style="text-align:right;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb">
					<span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;"><?=number_format($shippingItemRowTotal,2)?></span>                    
				</td>

			</tr>
			<?php 
				} // shipping charges 
				if($payment_charges > 0.00){
					//print_r($payment_charges);
					if($Items->payment_method=='cod' && $payment_charges > 0.00){
						$paymentorderItemQty=1;
						$payment_charge=$Items->payment_charge; // actual amount
						$payment_tax_percent=$Items->payment_tax_percent;// percentage
						$payment_tax_amount=$Items->payment_tax_amount; // aftercalculation only tax amount
						$payment_final_charge=$Items->payment_final_charge; // including tax amount

						// $paymentChargeItemRowTotal=$payment_charge * $orderItemQty;old
						$paymentChargeItemRowTotal=$payment_charge;
						// $paymentChargeItemRowTotal=$payment_charge * $ItemQty;//old

						if($payment_tax_percent > 0.00 && $payment_charge > 0.00) {
							// $payment_charge_RowExcTax = ($payment_charge*$orderItemQty);//old
							$payment_charge_RowExcTax = ($payment_charge);
							// $payment_charge_RowExcTax = ($payment_charge*$ItemQty);//old
							$payment_charge_tax_amount = ($payment_tax_percent / 100) * $payment_charge;
							$payment_charge_RowTaxAmount = ($payment_charge_tax_amount);
							// $payment_charge_RowTaxAmount = ($payment_charge_tax_amount*$orderItemQty);//old
							// $payment_charge_RowTaxAmount = ($payment_charge_tax_amount*$ItemQty); //old
							$payment_ItemRowTotal = $payment_charge + $payment_charge_RowTaxAmount;
						} else{						
							$payment_charge_RowExcTax=0;			
							$payment_ItemRowTotal=0;			
							$payment_charge_tax_amount=0;
							$payment_charge_RowTaxAmount =0;
						}
						// array pass percentage and amount
						if(!array_key_exists($payment_tax_percent, $taxArray)){
							$taxArray[$payment_tax_percent]=array();
							$sumArray[$payment_tax_percent]=array('final_tax_amount'=>0,'final_price'=>0);

						}		

						$sumArray[$payment_tax_percent]['final_tax_amount']=$sumArray[$payment_tax_percent]['final_tax_amount'] + $payment_charge_RowTaxAmount;
						$sumArray[$payment_tax_percent]['final_price']=$sumArray[$payment_tax_percent]['final_price'] + $payment_charge;

						// $codRowTotal_Sum += $payment_charge; //actual cod without tax
						$codRowTotal_Sum += $payment_ItemRowTotal; //include tax
						$codRowTotal_Sum_ex_tax += $payment_charge_RowExcTax; //include tax
						$codRowTotal_tax += $payment_charge_RowTaxAmount; //actual cod without tax


						// insert data invoicing details 
					$fbc_user_id=$this->session->userdata('LoginID');
					// invoiceing details data insert
					$insertinvoicingdetailsdata_cod=array(	
							'invoice_id'=>$invoice_id,
							'order_id'=>$order_id,
							'order_date'=>$order_create_date,
							'product_name'=>'COD Charges',
							// 'product_id'=>$product_id,
							// 'product_hsn_code'=>$product_hsn_code,
							// 'product_barcode'=>$product_barcode,
							// 'product_variants'=>$product_variants,
							// 'product_category'=>$product_category,
							'product_sku'=>'COD',
							'product_qty'=>$paymentorderItemQty,
							'product_price'=>$payment_charges,
							'place_of_supply'=>$shipto_state_code1,
							'gst_rates_applicable'=>$payment_tax_percent,// percentage
							'gst_amount'=>$payment_charge_tax_amount, //item tax
							'gst_row_amount'=>$payment_charge_RowTaxAmount,// item tax * qty
							//'total_row_amount'=>$total_row_amount,//
							'total_amount_excluding_gst'=>$payment_charge_RowExcTax,
							'total_amount_including_gst'=>$payment_ItemRowTotal, // gst_row_amount + total_amount_excluding_gst
							'created_by'=>$fbc_user_id,
							'created_at'=>time(),
							'ip'=>$_SERVER['REMOTE_ADDR']
					);
					$this->ShopProductModel->insertData('invoicing_details',$insertinvoicingdetailsdata_cod);

					}
			 ?>
			<tr>
				<td style="font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:5px 10px;margin:0;border-top:1px solid #ebebeb;text-align:center">
					<p style="font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;color:#000000; font-size: 12px;"><?=$i+1?></p>
				</td>
				<!-- <td style="text-align:center;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb; font-size: 12px; "><?=$item_order_increment_id?></td> -->
				<td style="text-align:center;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb; font-size: 12px; "><!-- <?=$product_hsn_code?> --></td>
				
				<td style="text-align:left;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb">
					<span style="font-family:Helvetica Neue,helvetica,sans-serif; font-size: 12px;">COD Charge</span>                    
				</td>
				<td style="text-align:center;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb; ">
					<span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;">COD</span>                    
				</td>
				<td style="text-align:center;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb">
					<span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;"><?=$payment_tax_percent?>%</span>                    
				</td>

				<td style="text-align:right;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb; text-align:center;">
					<span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;"><?=$paymentorderItemQty?></span>                    
					<!-- <span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;"><?=$ItemQty?></span>                     -->
				</td>
				<td style="text-align:right;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb;  text-align:center;">
					<span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;"><?=number_format($payment_charge,2)?></span>                    
				</td>
				<td style="text-align:right;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 10px;margin:0;border-top:1px solid #ebebeb">
					<span style="font-family:Helvetica Neue,helvetica,sans-serif;font-size: 12px;"><?=number_format($paymentChargeItemRowTotal,2)?></span>                    
				</td>

			</tr>
			<?php
				} // payment charges 

			   }//$inv_count end if
					/*if($shipping_charges > 0.00){ 
							$this->ShopProductModel->insertData('invoicing_details',$insertinvoicingdetailsdata_shipping);
						}
						if($Items->payment_method=='cod' && $payment_charges > 0.00){
							$this->ShopProductModel->insertData('invoicing_details',$insertinvoicingdetailsdata_cod);
						}*/

			//} // check array_in value 

		  } //for each
		?>
			
		</tbody>
	</table>
		
	
	<table cellpadding="0" cellspacing="0" border="0" style="width:680px;padding:0;margin:0;border-top:1px dashed #c3ced4;">
			<tbody>
				<tr>
					<td style="font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:20px 0px;margin:0;text-align:left;line-height:20px" width="40%">
						<!-- <?php if($payment_method=='cod' || $payment_method=='via_transfer' || $payment_method==''){?>
						<p style="font-family:helvetica;font-weight:normal;font-size:11px">Kindly make the payment online based on following:<br>
						BANK NAME : <?php  echo ((isset($user_shop_details->bank_name) && $user_shop_details->bank_name != null) ? $user_shop_details->bank_name : '' ); ?><br>
						A/C NAME : <?php  echo ((isset($user_shop_details->bank_acc_name) && $user_shop_details->bank_acc_name != null) ? $user_shop_details->bank_acc_name : '' ); ?><br>
						A/C NO : <?php  echo ((isset($user_shop_details->bank_acc_no) && $user_shop_details->bank_acc_no != null) ? $user_shop_details->bank_acc_no : '' ); ?><br>
						RTGS / NEFT IFSC : <?php  echo ((isset($user_shop_details->bank_ifsc) && $user_shop_details->bank_ifsc != null) ? $user_shop_details->bank_ifsc : '' ); ?><br>
						BIC/SWIFT : <?php  echo ((isset($user_shop_details->bic_swift) && $user_shop_details->bic_swift != null) ? $user_shop_details->bic_swift : '' ); ?><br>
						BRANCH : <?php  echo ((isset($user_shop_details->bank_branch) && $user_shop_details->bank_branch != null) ? $user_shop_details->bank_branch : '' ); ?><br>
						<?php } ?> -->
					</td>
					<td style="font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:20px 0px;margin:0;text-align:right;line-height:20px; padding-left: 25px;" width="60%">
						<table cellpadding="0" cellspacing="0" border="0" style="width:100%;padding:0;margin:0">
							<tbody>

							<?php
								// subtotal all amount
								$subtotalsumamount=$ItemRowTotal_Sum + $codRowTotal_Sum_ex_tax + $shippingRowTotal_Sum_ex_tax;

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
									// $actualAmount=$finalPrice/2;//old
									$actualAmount=$finalPrice;
									$actual_percentage1= ($actual * $actualAmount) / 100 ;
									$actual_percentage= $actual_percentage1;
									// final total
									$total_price_tax_sum += $actual_percentage + $actual_percentage;
									//$amount_deduct_tax1=$actualAmount - $actual_percentage;
									$amount_deduct_tax=$actualAmount;

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
												<?php echo ' @ '.$taxPercentage.'% on '.number_format($finalPrice,2);?>
												<!-- <?php echo $taxType1.' @ '.$taxPercentage.'% on '.$finalPrice;?> -->
											</td>
											<td align="right" style="padding:5px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;">
												<span style="font-family:Helvetica Neue,helvetica,sans-serif"><?=number_format($final_TaxAmount,2)?></span>
											</td>

										</tr>


							<?php

								}
							}

						}

								// $final_total_price_tax=$ItemRowTotal_Sum +$finaltaxAmountSum;
								/*$shippingRowTotal_tax=0; //shipping tax
								$codRowTotal_tax=0;*/ // cod tax
								$shipping_cod_charge_tax= $shippingRowTotal_tax + $codRowTotal_tax;
								// $final_sub_total_price_tax=$ItemRowTotal_Sum + $finaltaxAmountSum;
								$final_sub_total_price_tax=$subtotalsumamount + $finaltaxAmountSum;//new

								// $final_total_price_tax=($ItemRowTotal_Sum +$finaltaxAmountSum + $shipping_charges + $payment_charges) - $voucher_amount;//new  $codRowTotal_Sum $shippingRowTotal_Sum
								// $final_total_price_tax=($ItemRowTotal_Sum +$finaltaxAmountSum + $shippingRowTotal_Sum + $codRowTotal_Sum) - $voucher_amount;// now
								$final_total_price_tax = ($final_sub_total_price_tax) - $voucher_amount;// now after nick changes final

								if($final_sub_total_price_tax < $voucher_amount){
									//20 , 30
									$voucher_amount_remain = $voucher_amount - $final_sub_total_price_tax;
									$voucher_amount_used= $voucher_amount - $voucher_amount_remain;
									//$voucher_amount_remain=$voucher_amount;
									// $final_total_price_tax = ($final_sub_total_price_tax) - $voucher_amount;// now after nick changes final
									$final_total_price_tax = 0.00;// now after nick changes final
								}else{
									$final_total_price_tax = ($final_sub_total_price_tax) - $voucher_amount;// now after nick changes final
								}
							?>



								<tr style="padding-bottom:5px">
									<td  align="right" style="padding:5px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;width: 60%;     text-align: left;">
										TOTAL INCLUDING TAXES
									</td>
									<td align="right" style="padding:3px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;">
										<span style="font-family:Helvetica Neue,helvetica,sans-serif"><?=$currency.' '.number_format($final_sub_total_price_tax,2)?> </span>
									</td>
								</tr>
								<?php if($shippingRowTotal_Sum >0.00){ ?>
								<!-- <tr style="padding-bottom:5px">
									<td  align="right" style="padding:5px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;width: 60%;     text-align: left;">
										Shipping Charges
									</td>
									<td align="right" style="padding:3px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;">
										<span style="font-family:Helvetica Neue,helvetica,sans-serif"><?=$currency.' '.number_format($shippingRowTotal_Sum,2)?> </span>
									</td>
								</tr> -->
								<?php
									}
									if($codRowTotal_Sum >0.00){ //$codRowTotal_Sum $shippingRowTotal_Sum
								?>
								<!-- <tr style="padding-bottom:5px">
									<td  align="right" style="padding:5px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;width: 60%;     text-align: left;">
										Payment Charges
									</td>
									<td align="right" style="padding:3px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;">
										<span style="font-family:Helvetica Neue,helvetica,sans-serif"><?=$currency.' '.number_format($codRowTotal_Sum,2)?> </span>
									</td>
								</tr> -->
								<?php
									}

									if($voucher_amount >0.00){

										$displayvoucher_amount=0;
										if($final_sub_total_price_tax == $voucher_amount){
											$displayvoucher_amount=$voucher_amount;
										}elseif($final_sub_total_price_tax < $voucher_amount){
											$displayvoucher_amount=$final_sub_total_price_tax;
										}else{
											$displayvoucher_amount=$voucher_amount;
										}
								?>
									<tr style="padding-bottom:5px">
										<td  align="right" style="padding:5px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;width: 60%;     text-align: left;">
											Paid voucher
										</td>
										<td align="right" style="padding:3px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;">
											<span style="font-family:Helvetica Neue,helvetica,sans-serif"><?=$currency.' - '.number_format($displayvoucher_amount,2)?> </span>
										</td>
									</tr>
								<?php


									}//voucher end
									if($payment_method=='cod' || $payment_method=='via_transfer' || $payment_method==''){

										$balancedAmount=$final_total_price_tax;
									?>
								<tr>
									<td  align="right" style="padding:3px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;width: 60%;     text-align: left;">
										<strong>BALANCE DUE</strong>
									</td>
									<td align="right" style="padding:3px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;">
										<strong><span style="font-family:Helvetica Neue,helvetica,sans-serif"><?=$currency.' '.number_format($final_total_price_tax,2);?></span></strong>
									</td>
								</tr>
								<tr>
									<td  colspan="2" align="right" style="padding:3px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0; text-align:left;font-size:14px;">
										<?php
											echo $this->CommonModel->getIndianCurrencytoText($final_total_price_tax); ?>

									</td>
								</tr>
								<?php }else{ ?>
								<tr>
									<td  colspan="2" align="left" style="padding:3px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0; text-align:left;font-size:14px;">
										<?php
											echo 'Payment received through '.$payment_name.' '.$currency.' '.number_format($final_total_price_tax,2);
											$balancedAmount=0;
										?>

									</td>
								</tr>
								<tr>
									<td  align="right" style="padding:3px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;width: 60%;     text-align: left;">
										<strong>BALANCE DUE</strong>
									</td>
									<td align="right" style="padding:3px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0;font-size: 15px;">
										<strong><span style="font-family:Helvetica Neue,helvetica,sans-serif"><?=$currency.' 0';?></span></strong>
									</td>
								</tr>
								<tr>
									<td  colspan="2" align="right" style="padding:3px 9px;font-family:helvetica;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0; text-align:left;font-size:14px;">
										<?php
											//echo $this->CommonModel->getIndianCurrencytoText($final_total_price_tax); ?>

									</td>
								</tr>
								<?php } ?>

							</tbody>
						</table>
					</td>

					<?php

						//balanced amount updated invoicing
						$balancedAmountInvoice=$balancedAmount;
						// update invoice table
						$invoice_subtotal=$ItemRowTotal_Sum;
						$invoice_tax=$finaltaxAmountSum;
						$invoice_grand_total=$final_total_price_tax;
						$invoice_update=array('invoice_subtotal'=>$invoice_subtotal,'invoice_tax'=>$invoice_tax,'invoice_grand_total'=>$invoice_grand_total,'voucher_used_amount'=>$voucher_amount_used,'voucher_remain_amount'=>$voucher_amount_remain,'invoice_balanced_amount'=>$balancedAmountInvoice,'updated_at'=>time());
						$where_invoice_arr=array('id'=>$invoice_id);
						$invoioceUpdated=$this->ShopProductModel->updateData('invoicing',$where_invoice_arr,$invoice_update);

					?>
				</tr>
			</tbody>
	</table>

	<?php if(!empty($invoice_bottom_message_value)){?>
	<table cellspacing="2" style="width:100%;border-top:1px solid #ccc">
		<tr>
			<td style="height:45px;padding:10px 13px;text-align:center">	
				<p><?=$invoice_bottom_message_value?></p>
			</td>
			
		</tr>
	</table>
	<?php } ?>
		
      <!-- table 1 end -->
  </body>
</html>
