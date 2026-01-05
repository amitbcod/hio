<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Catalog Builder</title>
<style>
body{font-size: 14px;
    font-family: helvetica;
    color: #000000;padding:10px;}
.table-block{
	border: 1px solid #e0e0e0;
    width: 100%;
    max-width: 765px;
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
@media screen and (max-width:991px){
	table tr td ul li{width:48% !important;margin-bottom: 20px !important;}
	table.table-block {width: 100% !important;}
	.base-price{min-width:50px;}
    .fare-price{min-width:45px;}
	}
@media screen and (max-width:640px){
	table tr td ul li{width:99% !important;}
	}

</style>

</head>

<body style="background-color:#ffffff; font-family: helvetica;">
<table class="table-block" bgcolor="FFFFFF" cellspacing="0" cellpadding="0" border="0" width="1030" style="width:1030px;max-width:1030px;margin:0 auto;background:#fff;font-size:13px;font-family:helvetica;color:#000000; border:0;">
 <tbody>
	<tr>
		<td>
			<table  border="0" cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
				<td>
					<?php if (isset($catloglbuilderData->catalog_name)) { ?>
						<p style="line-height: 20px;font-size:18px;font-family:helvetica;color:#000000;margin-bottom:20px;"><strong>Catalog Name :</strong>  <?php echo $catloglbuilderData->catalog_name; ?></p>
					<?php } ?>
					<?php if (isset($catloglbuilderData->customer_name)) { ?>
						<p style="line-height: 20px;font-size:18px;font-family:helvetica;color:#000000;margin-bottom:20px;"><strong>Customer Name :</strong> <?php echo $catloglbuilderData->customer_name; ?> </p>
					<?php } ?>
					<?php if (isset($catloglbuilderData->email)) { ?>
						<p style="line-height: 20px;font-size:18px;font-family:helvetica;color:#000000;margin-bottom:20px;"><strong>Email Address :</strong>  <?php echo $catloglbuilderData->email; ?></p>
					<?php } ?>
					<?php if (isset($catloglbuilderData->phone_no)) { ?>
						<p style="line-height: 20px;font-size:18px;font-family:helvetica;color:#000000;margin-bottom:20px;"><strong>Phone No :</strong>  <?php echo $catloglbuilderData->phone_no; ?></p>
					<?php  } ?>
				</td>
				</tr>
			</table>
		</td>
	</tr>

<?php  if (isset($shop_category) && !empty($shop_category)) {
    // echo "<pre>";print_r($shop_category);?>
<?php foreach ($shop_category as $main_cat) {
        if ($main_cat->product_count !=0) {
            ?>

	<?php if (isset($main_cat->menu_level_1)) { ?>

		<?php foreach ($main_cat->menu_level_1 as $menu_level_1) {
                if ($menu_level_1->product_count1 !=0) {
                    ?>


	<tr>
		<td>
			<h2 style="font-size: 22px;font-family:helvetica;color:#000000;margin-bottom: 10px;margin-top: 20px;"><?=lang('zumbashop_catalog')?> <?php // echo isset($catloglbuilderData->catalog_name) ? $catloglbuilderData->catalog_name : '' ;?> -<?php echo $main_cat->menu_name ?> - <?php echo $menu_level_1->menu_name; ?> </h2>
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td width="100%">
						<ul style="margin:0;padding:0;list-style:none;">
						<?php if (isset($menu_level_1->final_product_data1) && !empty($menu_level_1->final_product_data1)) {
                        ?>

								<?php  foreach ($menu_level_1->final_product_data1 as $product) { ?>

						<li style="width:31.6666%; display:inline-block; margin-right:1%;margin-bottom:2%; vertical-align:top;">
						<table width="100%" cellpadding="0" cellspacing="0" style=" border: 1px solid #e2e2e2; font-family:helvetica;color:#000000;">
							<tr>
								<td height="170" style="width:50%; text-align: center;">
									<?php
                                    if ($product->real_product_data1->{0}->base_image != '') {
                                        $product_image=PRODUCT_MEDIUM_IMG.$product->real_product_data1->{0}->base_image;
                                    } else {
                                        $product_image=PRODUCT_DEFAULT_IMG;
                                    }
                                    ?>
									<img src= "<?php echo $product_image;?>" style="max-width: 160px;max-height: 150px;">
								</td>
								<td  height="170" style="vertical-align: middle;width:50%;padding-right: 10px;">
									<h3 style="margin-top: 10px;margin-bottom: 10px;"><?php echo $product->real_product_data1->{0}->name; ?></h3>
								</td>
							</tr>


							<tr>
								<td style="background-color:#e1e1e1;" colspan="2">
									<table bgcolor="#e1e1e1" border="0" cellpadding="3" cellspacing="0" width="100%" style="border-color: #bcbcbc;font-family:helvetica;color:#000000;font-size: 9px;background-color:#e1e1e1;text-align:left; border-left:1px solid #e2e2e2; border-right:1px solid #e2e2e2;">
										<tr style="background-color:#e1e1e1;">
											<?php if ($product->real_product_data1->{0}->product_type != 'simple') {
                                        $variantName='' ?>
											<!-- <th width="70" style="border-bottom: 1px solid #bcbcbc; text-align:left;">Color</th>
											<th width="20" style="border-bottom: 1px solid #bcbcbc;text-align:left;">Size</th> -->
								<?php if (isset($product->VariantNameArr) && $product->VariantNameArr != '') {
                                            foreach ($product->VariantNameArr as $pk=>$single_variantName) {
                                                $variantName.= '<th width="20" style="border-bottom: 1px solid #bcbcbc;text-align:left;">'.$single_variantName.'</th>';
                                                // print_r($single_variantName);
                                            }
                                        } ?>

								<?php if (isset($product->VariantNameArr) && $product->VariantNameArr != '') { ?>
									<?php echo rtrim($variantName, ", "); ?>

									<?php } ?>

										<?php
                                    } ?>
										<?php if ($catloglbuilderData->show_upc == 1) { ?>
											<th style="border-bottom: 1px solid #bcbcbc;text-align:left;">Barcode</th>
										<?php } ?>
										<?php if ($catloglbuilderData->show_qtys == 1  || $catloglbuilderData->show_csv_qtys == 1) { ?>
											<th width="110" style="border-bottom: 1px solid #bcbcbc;text-align:left;">Qty</th>
										<?php } ?>
											<?php if ($catloglbuilderData->show_retail_price == 1) { ?>
											<th class="base-price" style="border-bottom: 1px solid #bcbcbc;text-align:left;">Base Price</th>
											<?php } ?>
											<?php if ($catloglbuilderData->show_csv_price == 1) { ?>
											<th class="fare-price" style="border-bottom: 1px solid #bcbcbc;text-align:left;">Price</th>
											<?php } ?>
										</tr>

									<?php if ($product->real_product_data1->{0}->product_type == 'simple') { ?>
										<tr style="background-color:#e1e1e1;">
											<?php if ($catloglbuilderData->show_upc == 1) { ?>
											<td><?php echo   $product->catalog_builder_items->upc;  ?></td>
											<?php } ?>
											<?php if ($catloglbuilderData->show_qtys == 1  || $catloglbuilderData->show_csv_qtys == 1) { ?>
												<td>
													<?php echo  ($catloglbuilderData->show_csv_qtys == 1) ? '('.$product->catalog_builder_items->quantity.')' :'' ?>
													<?php echo  ($catloglbuilderData->show_qtys== 1 && $catloglbuilderData->show_csv_qtys == 1) ? '-' :'' ?>
													<?php echo  ($catloglbuilderData->show_qtys== 1) ? '('.$product->catalog_builder_items->available_quantity.')' :'' ?>
												</td>
											<?php  } ?>
										<?php if ($catloglbuilderData->show_retail_price == 1) { ?>
											<td><strong><?php echo (isset($currency_symbol) && $currency_symbol != '') ? $currency_symbol :'' ?> <?php echo isset($product->catalog_builder_items->webshop_price) ? $product->catalog_builder_items->webshop_price : ''; ?></strong></td>
										<?php } ?>
										<?php if ($catloglbuilderData->show_csv_price == 1) { ?>
											<td><strong><?php echo (isset($currency_symbol) && $currency_symbol != '') ? $currency_symbol :'' ?> <?php echo isset($product->catalog_builder_items->price) ? $product->catalog_builder_items->price : ''; ?></strong></td>
										<?php } ?>
										</tr>
									<?php  } else { ?>

										<?php foreach ($product->catalog_builder_items as $variant) {

											$product_variants= (($variant->product_variants != '') ? json_decode($variant->product_variants) : '');


                                            $variants = '';
                                            if (isset($product_variants) && $product_variants != '') {
                                                foreach ($product_variants as $pk=>$single_variant) {
                                                    foreach ($single_variant as $key=>$val) {
                                                        $variants.= '<td width="70">'.$val.'</td>';
                                                    }
                                                }
                                            } ?>
										<tr style="background-color:#e1e1e1;">

									<?php if (isset($product_variants) && $product_variants != '') { ?>
										<?php echo rtrim($variants, ", "); ?>

									<?php } ?>

											<?php if ($catloglbuilderData->show_upc == 1) { ?>
												<td><?php echo $variant->upc;  ?></td>
											<?php } ?>
										<?php if ($catloglbuilderData->show_qtys == 1  || $catloglbuilderData->show_csv_qtys == 1) { ?>
											<td width="110">
												<?php echo ($catloglbuilderData->show_csv_qtys== 1) ? '('.$variant->quantity.')' : ''; ?>
											 	<?php echo  ($catloglbuilderData->show_qtys== 1 && $catloglbuilderData->show_csv_qtys== 1) ? '-' :'' ?>
											 	<?php echo  ($catloglbuilderData->show_qtys== 1) ? '('.$variant->available_quantity.')' :'' ?>
											 </td>
										<?php } ?>
										<?php if ($catloglbuilderData->show_retail_price == 1) { ?>
											<td><strong><?php echo (isset($currency_symbol) && $currency_symbol != '') ? $currency_symbol :'' ?> <?php echo isset($variant->webshop_price) ? $variant->webshop_price : ''; ?></strong></td>
										<?php } ?>
										<?php if ($catloglbuilderData->show_csv_price == 1) { ?>
											<td><strong><?php echo (isset($currency_symbol) && $currency_symbol != '') ? $currency_symbol :'' ?> <?php echo isset($variant->price) ? $variant->price : ''; ?></strong></td>
										<?php } ?>
										</tr>
										<?php
                                        } ?>
									<?php
                                    } ?>

									</table>
								</td>
							</tr>

							<tr>
								<td colspan="2">
									<table cellpadding="3" cellspacing="0" width="100%" style="border: 0;font-family:helvetica;color:#000000;font-size: 13px;font-weight:600;">
										<tr>
											<?php if ($catloglbuilderData->show_coll_name == 1) { ?>
											<td style="padding:15px 10px;border-right: 1px solid #dcdcdc; border-top:1px solid #dcdcdc"><?php echo isset($product->real_product_data1->{'collection_name'}) ? $product->real_product_data1->{'collection_name'} :''; ?></td>
											<?php } ?>

											<?php if ($catloglbuilderData->show_style_code == 1) { ?>
											<td style="text-align:center;padding:15px 10px; border-top:1px solid #dcdcdc">
												<?php echo isset($product->real_product_data1->{0}->product_code) ? $product->real_product_data1->{0}->product_code :''; ?></td>
											<?php } ?>
										</tr>
									</table>
								</td>
							</tr>
						</table>
						</li>


						<?php }
                    } //}?>

						</ul>

					</td>
				</tr>
			</table>
		</td>
	</tr>

	<?php
                }
            } } else { ?>



	<tr>
		<td>
			<h2 style="font-size: 22px;font-family:helvetica;color:#000000;margin-bottom: 10px;margin-top: 20px;"><?=lang('zumbashop_catalog')?>  -<?php echo $main_cat->menu_name ?> </h2>
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td width="100%">
						<ul style="margin:0;padding:0;list-style:none;">
							<?php if (isset($main_cat->final_product_data) && !empty($main_cat->final_product_data)) { ?>
								<?php  foreach ($main_cat->final_product_data as $product) { ?>

						<li style="width:31.6666%; display:inline-block; margin-right:1%;margin-bottom:2%; vertical-align:top;">
						<table width="100%" cellpadding="0" cellspacing="0" style=" border: 1px solid #e2e2e2; font-family:helvetica;color:#000000;">
							<tr>

								<td height="170" style="width:50%; text-align: center;">

									<?php if ($product->real_product_data1->{0}->base_image != '') { ?>
										<img src= "<?php echo PRODUCT_THUMB_IMG.$product->real_product_data1->{0}->base_image;?>" style="max-width: 130px;max-height: 150px;">
									<?php } else { ?>
										<img src= "<?php echo BASE_URL."/public/images/default_product_image.png"?>" style="max-width: 130px;max-height: 150px;">
									<?php }  ?>
								</td>
								<td  height="170" style="vertical-align: middle;width:50%;padding-right: 10px;">
									<h3 style="margin-top: 10px;margin-bottom: 10px;"><?php echo $product->real_product_data1->{0}->name; ?></h3>
								</td>
							</tr>

							<tr>
								<td style="background-color:#e1e1e1;" colspan="2">
									<table bgcolor="#e1e1e1" border="0" cellpadding="3" cellspacing="0" width="100%" style="border-color: #bcbcbc;font-family:helvetica;color:#000000;font-size: 9px;background-color:#e1e1e1;text-align:left; border-left:1px solid #e2e2e2; border-right:1px solid #e2e2e2;">
										<tr style="background-color:#e1e1e1;">
											<?php if ($product->real_product_data1->{0}->product_type != 'simple') { ?>
							<?php $variantName='';
                             if (isset($product->VariantNameArr) && $product->VariantNameArr != '') {
                                 // print_r($product->VariantNameArr);die();
                                 foreach ($product->VariantNameArr as $pk=>$single_variantName) {
                                     $variantName.= '<th width="20" style="border-bottom: 1px solid #bcbcbc;text-align:left;">'.$single_variantName.'</th>';
                                     // print_r($single_variantName);
                                 }
                             } ?>

								<?php if (isset($product->VariantNameArr) && $product->VariantNameArr != '') { ?>
									<?php echo rtrim($variantName, ", "); ?>

									<?php } ?>

										<?php  } ?>
										<?php if ($catloglbuilderData->show_upc == 1) { ?>
											<th style="border-bottom: 1px solid #bcbcbc;text-align:left;">Barcode</th>
										<?php } ?>
										<?php if ($catloglbuilderData->show_qtys == 1  || $catloglbuilderData->show_csv_qtys == 1) { ?>
											<th width="110" style="border-bottom: 1px solid #bcbcbc;text-align:left;">Qty</th>
										<?php } ?>

											<?php if ($catloglbuilderData->show_retail_price == 1) { ?>
											<th style="border-bottom: 1px solid #bcbcbc;text-align:left;">Base Price</th>
											<?php } ?>
											<?php if ($catloglbuilderData->show_csv_price == 1) { ?>
											<th style="border-bottom: 1px solid #bcbcbc;text-align:left;">Price</th>
											<?php } ?>
										</tr>

									<?php if ($product->real_product_data1->{0}->product_type == 'simple') { ?>
										<tr style="background-color:#e1e1e1;">
											<?php if ($catloglbuilderData->show_upc == 1) { ?>
											<td><?php echo   $product->catalog_builder_items->upc;  ?></td>
											<?php } ?>
											<?php if ($catloglbuilderData->show_qtys == 1  || $catloglbuilderData->show_csv_qtys == 1) { ?>
												<td>
													<?php echo  ($catloglbuilderData->show_csv_qtys== 1) ? '('.$product->catalog_builder_items->quantity.')' :'' ?>
													<?php echo  ($catloglbuilderData->show_qtys== 1 && $catloglbuilderData->show_csv_qtys == 1) ? '-' :'' ?>
													<?php echo  ($catloglbuilderData->show_qtys== 1) ? '('.$product->catalog_builder_items->available_quantity.')' :'' ?>
												</td>
											<?php  } ?>
										<?php if ($catloglbuilderData->show_retail_price == 1) { ?>
											<td><strong><?php echo (isset($currency_symbol) && $currency_symbol != '') ? $currency_symbol :'' ?> <?php echo isset($product->catalog_builder_items->webshop_price) ? $product->catalog_builder_items->webshop_price : ''; ?></strong></td>
										<?php } ?>
										<?php if ($catloglbuilderData->show_csv_price == 1) { ?>
											<td><strong><?php echo (isset($currency_symbol) && $currency_symbol != '') ? $currency_symbol :'' ?> <?php echo isset($product->catalog_builder_items->price) ? $product->catalog_builder_items->price : ''; ?></strong></td>
										<?php } ?>
										</tr>
									<?php  } else {
                                 // print_r($menu_level_1->final_product_data1->catalog_builder_items);//die();?>

										<?php foreach ($product->catalog_builder_items as $variant) {
                                     $product_variants = '';
                                     if ($variant->product_variants != '') {
                                         $product_variants = json_decode($variant->product_variants);
                                     }


                                     $variants = '';
                                     if (isset($product_variants) && $product_variants != '') {
                                         foreach ($product_variants as $pk=>$single_variant) {
                                             foreach ($single_variant as $key=>$val) {
                                                 $variants.= '<td width="70">'.$val.'</td>';
                                             }
                                         }
                                     } ?>
										<tr style="background-color:#e1e1e1;">

									<?php if (isset($product_variants) && $product_variants != '') { ?>
										<?php echo rtrim($variants, ", "); ?>

									<?php } ?>
											<?php if ($catloglbuilderData->show_upc == 1) { ?>
												<td><?php echo $variant->upc;  ?></td>
											<?php } ?>
										<?php if ($catloglbuilderData->show_qtys == 1  || $catloglbuilderData->show_csv_qtys == 1) { ?>
											<td width="110" >
												<?php echo ($catloglbuilderData->show_csv_qtys== 1) ? '('.$variant->quantity.')' : ''; ?>
											 	<?php echo  ($catloglbuilderData->show_qtys== 1 && $catloglbuilderData->show_csv_qtys== 1) ? '-' :'' ?>
											 	<?php echo  ($catloglbuilderData->show_qtys== 1) ? '('.$variant->available_quantity.')' :'' ?>
												</td>
										<?php } ?>
										<?php if ($catloglbuilderData->show_retail_price == 1) { ?>
											<td><strong><?php echo (isset($currency_symbol) && $currency_symbol != '') ? $currency_symbol :'' ?> <?php echo isset($variant->webshop_price) ? $variant->webshop_price : ''; ?></strong></td>
										<?php } ?>
										<?php if ($catloglbuilderData->show_csv_price == 1) { ?>
											<td><strong><?php echo (isset($currency_symbol) && $currency_symbol != '') ? $currency_symbol :'' ?> <?php echo isset($variant->price) ? $variant->price : ''; ?></strong></td>
										<?php } ?>
										</tr>
										<?php
                                 } ?>
									<?php
                             } ?>

									</table>
								</td>
							</tr>

							<tr>
								<td colspan="2">
									<table cellpadding="3" cellspacing="0" width="100%" style="border: 0;font-family:helvetica;color:#000000;font-size: 13px;font-weight:600;">
										<tr>
											<?php if ($catloglbuilderData->show_coll_name == 1) { ?>
											<td style="padding:15px 10px;border-right: 1px solid #dcdcdc; border-top:1px solid #dcdcdc"><?php echo isset($product->real_product_data1->collection_name) ? $product->real_product_data1->collection_name :''; ?></td>
											<?php } ?>

											<?php if ($catloglbuilderData->show_style_code == 1) { ?>
											<td style="text-align:center;padding:15px 10px; border-top:1px solid #dcdcdc">
												<?php echo isset($product->real_product_data1->{0}->product_code) ? $product->real_product_data1->{0}->product_code :''; ?></td>
											<?php } ?>
										</tr>
									</table>
								</td>
							</tr>
						</table>
						</li>
					<?php  } } ?>

						</ul>
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<?php } ?>
<?php
        }
    }
} ?>


 </tbody>
 </table>




</body>
</html>
