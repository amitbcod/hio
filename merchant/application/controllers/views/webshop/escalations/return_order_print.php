<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="generator" content="Jekyll v4.1.1">
    <title>Webshop-returns-order print</title>



    <!-- Bootstrap core CSS -->
    <link href="<?php echo SKIN_CSS; ?>css/bootstrap.min.css" rel="stylesheet">

     <script src="https://code.jquery.com/jquery-2.2.0.min.js" type="text/javascript"></script>
    <script src="<?php echo SKIN_JS; ?>js/bootstrap.min.js"></script>
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;500;600;700;800&display=swap" rel="stylesheet">
	<link href="<?php echo SKIN_CSS; ?>css/all.css" rel="stylesheet">

    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
	  table.table-style{
	  font-family: 'Montserrat', sans-serif;
    font-size: 14px;
    margin: 10px auto 0px;
    text-align: left;
    padding: 0 0px;
    border: 1px solid #dee2e6;
    border-collapse: collapse;
	  }
	  table.table-style th{
	  border: 1px solid #dee2e6;
    padding: .75rem;
    vertical-align: middle;
    font-weight: 600;
    text-transform: uppercase;
    white-space: nowrap;
    border-bottom: 2px solid #dee2e6;
    font-size: 14px;
    color: #ffffff;
    text-align: center;
	  }
	 table.table-style thead{
	 background: linear-gradient(90deg, rgb(203, 31, 83) 0%, rgb(115, 16, 91) 84.41%);
	color: rgb(255, 255, 255);
	  }
	  table.table-style td{
	  border: 1px solid #dee2e6;
    padding: .75rem;
    vertical-align: middle;
    font-weight: 500;
    color: #212529;
    font-size: 14px;
    text-align: center;
	  }
	  .form-control{
	      font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: .25rem;
    transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    padding: .375rem .75rem;
    width: 270px;
    height: 35px;
    font-size: 14px;
	  }
	  i.fa.fa-fw.fa-sort {
    margin-top: 4px;
}
    </style>


    </head>
	<body style="margin:0; padding:0;vertical-align:top; font-family: 'Montserrat', sans-serif; font-size: 14px; background:#fff; font-weight:400;color:#444444;">
		<table cellpadding="0" cellspacing="0" style="font-family:'Montserrat', sans-serif;font-size: 14px;margin:0 auto;text-align:left;padding: 0px 0px;background-color:#f5f5f5;" width="100%" align="center">
			<tbody>
				<tr>
					<td>
						<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:0 auto;text-align:left;padding: 0 0px;background-color:#ffffff;" width="900" align="center">
							<tbody>
								<tr>
									<td style="padding:0 40px;">

										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:50px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center">

											<tbody>
											<tr>
												<td style="padding:0;vertical-align: middle; text-align: left;" width="100%">
														<img src="<?php echo base_url(); ?>public/images/shopinshop-logo.png" width="164">
												</td>
												<td style="padding:0;vertical-align: middle; text-align: right;" width="100%">
														<img src="<?php echo getBarcodeUrl($ReturnOrderData->return_order_barcode); ?>" width="132">
												</td>
											</tr>
											</tbody>
										</table>

										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:40px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center">

											<tbody>
											<tr>
												<td style="padding:0;vertical-align: middle; text-align: left;" width="100%">
													<h1 style="font-weight: 600;font-size: 18px;line-height: 22px;letter-spacing: 0.05em;text-transform: capitalize;color: #444444;font-family: 'Montserrat', sans-serif;">Order Return Details</h1>
												</td>
											</tr>


											</tbody>
										</table>

										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:40px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center">

											<tbody>
											<tr>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;">Order Number</span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php echo $ReturnOrderData->return_order_increment_id; ?></span>
												</td>
												<td width="10%"></td>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;">Customer Name</span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php echo $OrderData->customer_firstname.' '.$OrderData->customer_lastname; ?></span>
												</td>

											</tr>

											<tr>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;">Purchased on</span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php  echo date(SIS_DATE_FM,$OrderData->created_at); ?></span>
												</td>
												<td width="10%"></td>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;">Shipping Address</span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php
														if(isset($ShippingAddress) && $ShippingAddress->address_id!=''){
															echo $this->WebshopOrdersModel->getFormattedAddress($ShippingAddress);
														}else{
															echo '-';
														}
														?></span>
												</td>
											</tr>

											<tr>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;">Order Status</span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php echo $this->CommonModel->getReturnOrderStatusLabel($ReturnOrderData->status);?></span>
												</td>
												<td width="10%"></td>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">

												</td>
											</tr>



											</tbody>
										</table>





										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:40px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center">

											<tbody>

											<tr>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="33%">
													<span style="display: inline-block;width: 170px; vertical-align: top;">Return Request Date</span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php  echo date(SIS_DATE_FM,$ReturnOrderData->created_at); ?></span>
												</td>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="33%">
													<span style="display: inline-block;width: 200px; vertical-align: top;">Return Request Due Date</span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php  echo date(SIS_DATE_FM,$ReturnOrderData->return_request_due_date); ?></span>
												</td>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="33%">
													<span style="display: inline-block;width: 170px; vertical-align: top;">Return Recieved Date</span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php echo (isset($ReturnOrderData->return_recieved_date) && $ReturnOrderData->return_recieved_date!='' && $ReturnOrderData->return_recieved_date!=0)?date('d/m/Y',$ReturnOrderData->return_recieved_date):''?></span>
												</td>
											</tr>


											</tbody>
										</table>





										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:55px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center">

											<tbody>
											<tr>
												<td style="padding:0;vertical-align: middle; text-align: left;padding:0 0 10px;" width="100%">
													<h2 style="font-weight: 500;font-size: 16px;line-height: 22px;letter-spacing: 0.05em;text-transform: capitalize;color: #444444;font-family: 'Montserrat', sans-serif;">Customer Return Reason</h1>
												</td>
											</tr>
											<tr>
												<td style="padding:0;vertical-align: middle; text-align: left;" width="100%">
													<p style="font-weight: 400;line-height: 1.5;color: #495057; width: 100%;font-size:14px;"><?php echo $ReturnOrderData->reason_for_return ?></p>
												</td>
											</tr>


											</tbody>
										</table>



										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:60px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center">

											<tbody>
											<tr>
												<td style="padding:0;vertical-align: middle; text-align: left;" width="100%">
													<h1 style="font-weight: 600;font-size: 18px;line-height: 22px;letter-spacing: 0.05em;text-transform: capitalize;color: #444444;font-family: 'Montserrat', sans-serif;">Return Products</h1>
												</td>
											</tr>

											</tbody>
										</table>



										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:10px auto 30px;text-align:left;padding: 0 0px;border:1px solid #dee2e6;border-collapse: collapse; " width="" align="center" class="table-style">

											<thead style="background: linear-gradient(90deg, rgb(203, 31, 83) 0%, rgb(115, 16, 91) 84.41%);color: rgb(255, 255, 255);">
											<tr>
											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">PRODUCT NAME </th>
											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">VARIANTS </th>
											 <!--  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">SIZE </th> -->
											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">QTY REQUESTED </th>
											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">QTY RECIEVED </th>
											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">QTY APPROVED </th>
											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">PRIECE/PIECE </th>
											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">TOTAL PRICE </th>
											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">RESTOCK </th>
											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">TYPE </th>
											</tr>
										  </thead>

											<tbody>
												<?php foreach ($OrderItems as $OrderItem) {
			$total_price=0;
				 if($OrderItem->qty_return_recieved<=0){
				   $total_price=0;
			  }else{
				   $total_price=$OrderItem->price * $OrderItem->qty_return_recieved;
			  }
				$variant_html='';
			  if($OrderItem->product_type=='conf-simple'){
				  $product_variants=$OrderItem->product_variants;
				  if(isset($product_variants) && $product_variants!=''){
					$variants=json_decode($product_variants, true);
					if(isset($variants) && count($variants)>0){


						foreach($variants as $pk=>$single_variant){
							foreach($single_variant as $key=>$val){

							$variant_html.='<span class="variant-item">'.$key.' - '.$val.'</span><br>';

							}
						}
					}
				  }else{
					 $variants='-';
				  }
			  }else{
				  $variants='-';
			  }
												 ?>
													<tr>
											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo $OrderItem->product_name; ?></td>
											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo ($OrderItem->product_type=='conf-simple')?$variant_html:'-'; ?></td>
											 <!--  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;">UK 10</td> -->
											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo $OrderItem->qty_return; ?></td>
											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo $OrderItem->qty_return_recieved; ?></td>
											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo $OrderItem->qty_return_approved; ?></td>
											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo $currency_code.' '.number_format($OrderItem->price,2); ?></td>
											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo ($OrderItem->qty_return_recieved<=0)?'0':$currency_code.' '.number_format($total_price,2); ?></td>
											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;">

												<label class="checkbox">
													<input type="checkbox"  name="options" autocomplete="off" readonly  <?php echo ($OrderItem->is_restock==1)?'checked':''; ?>>
													<span class="checked"></span>
												</label>

											</td>
											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo $OrderItem->product_inv_type; ?> </td>
											</tr>

												<?php } ?>

										  </tbody>
										</table>




										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:40px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center">

											<tbody>
											<tr>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;">Refund Approved</span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php echo $currency_code.' '.number_format($ReturnOrderData->order_grandtotal_approved,2); ?></span>
												</td>
												<td width="10%"></td>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">

												</td>

											</tr>
											</tbody>
										</table>


									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>


	</body>
</html>
