<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="generator" content="Jekyll v4.1.1">
    <title>Print Order Return  - <?php echo $OrderData->return_order_increment_id; ?></title>



    <!-- Bootstrap core CSS -->
    <link href="<?php echo SKIN_CSS; ?>bootstrap.min.css" rel="stylesheet">

     <script src="https://code.jquery.com/jquery-2.2.0.min.js" type="text/javascript"></script>
    <script src="<?php echo SKIN_JS; ?>bootstrap.min.js"></script>
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;500;600;700;800&display=swap" rel="stylesheet">
	<link href="<?php echo SKIN_CSS; ?>all.css" rel="stylesheet">

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
												<img src="<?php echo SITE_LOGO; ?><?php echo "" ?>" width="164">
												</td>
												<td style="padding:0;vertical-align: middle; text-align: right;" width="100%">
													<img src="<?php echo getBarcodeUrl($OrderData->return_order_barcode); ?>" width="300">
												</td>
											</tr>
											</tbody>
										</table>

										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:40px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center">

											<tbody>
											<tr>
												<td style="padding:0;vertical-align: middle; text-align: left;" width="100%">
													<h1 style="font-weight: 600;font-size: 18px;line-height: 22px;letter-spacing: 0.05em;text-transform: capitalize;color: #444444;font-family: 'Montserrat', sans-serif;"><?=lang('order_details')?></h1>
												</td>
											</tr>


											</tbody>
										</table>

										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:40px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center">

											<tbody>
											<tr>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;"><?=lang('return_order_number')?></span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php echo $OrderData->return_order_increment_id; ?></span>
												</td>
												<td width="10%"></td>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;"><?=lang('customer_number')?></span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php echo $OrderData->customer_name; ?></span>
												</td>

											</tr>

											<tr>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;"><?=lang('return_initiated_on')?></span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php echo  date('d/m/Y', $OrderData->created_at); ?> | <?php echo  date('h:i A', $OrderData->created_at); ?></span>
												</td>
												<td width="10%"></td>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;"><?=lang('webshop_name')?></span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php echo $OrderData->webshop_name; ?></span>
												</td>
											</tr>


											<tr>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;"><?=lang('address_to_return')?></span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php if (isset($returnAddress)) {
    echo $returnAddress;
} ?></span>
												</td>
												<td width="10%"></td>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;">&nbsp;</span> <span style="font-weight:600;display: inline-block; vertical-align: top;">&nbsp;</span>
												</td>
											</tr>



											</tbody>
										</table>




										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:60px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center">

											<tbody>
											<tr>
												<td style="padding:0;vertical-align: middle; text-align: left;" width="100%">
													<h1 style="font-weight: 600;font-size: 18px;line-height: 22px;letter-spacing: 0.05em;text-transform: capitalize;color: #444444;font-family: 'Montserrat', sans-serif;"><?=lang('products')?> </h1>
												</td>
											</tr>


											</tbody>
										</table>


										<?php if (isset($OrderData->order_items) && count($OrderData->order_items)>0) {?>
										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:10px auto 100px;text-align:left;padding: 0 0px;border:1px solid #dee2e6;border-collapse: collapse;" width="100%" align="center" class="table-style">

											<thead style="background: linear-gradient(90deg, rgb(203, 31, 83) 0%, rgb(115, 16, 91) 84.41%);color: rgb(255, 255, 255);">
											<tr>
											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;"><?=lang('product_name')?></th>
											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;"><?=lang('variants')?> </th>

											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;"><?=lang('qty_returned')?></th>
											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;"><?=lang('total_price')?></th>
											</tr>
										  </thead>

											<tbody>
											<?php foreach ($OrderData->order_items as $value) {
    $product_variants = '';
    if (isset($value->product_variants) && $value->product_variants != '') {
        $product_variants = json_decode($value->product_variants);
    }

    $variants =array();
    if (isset($product_variants) && $product_variants != '') {
        foreach ($product_variants as $pk=>$single_variant) {
            foreach ($single_variant as $key=>$val) {
                $variants[]=$key.' : '.$val.' ';
            }
        }
    } else {
        $variants[]='-';
    } ?>
											<tr>
											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo $value->product_name; ?></td>
											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo implode(', ', $variants); ?></td>

											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo $value->qty_return; ?></td>
											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo CURRENCY_TYPE; ?> <?php echo number_format(($value->price*$value->qty_return), 2); ?></td>
											</tr>
										<?php
}
                                        }
                                        ?>


										  </tbody>

										</table>

										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:55px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center">

											<tbody>
											<tr>
												<td style="padding:0;vertical-align: middle; text-align: left;padding:0 0 10px;" width="100%">
													<h2 style="font-weight: 500;font-size: 16px;line-height: 22px;letter-spacing: 0.05em;text-transform: capitalize;color: #444444;font-family: 'Montserrat', sans-serif;"><?=lang('reason_for_the_return')?></h2>
												</td>
											</tr>
											<tr>
												<td style="padding:0;vertical-align: middle; text-align: left;" width="100%">
													<textarea class="form-control" type="number"  readonly value="" placeholder="Message" style="font-weight: 400;line-height: 1.5;color: #495057;background-color: #fff;background-clip: padding-box;border: 1px solid #ced4da;border-radius: .25rem;transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;padding: .375rem .75rem; width: 100%;height: 120px;font-size:14px;" spellcheck="false"><?php echo $OrderData->reason_for_return; ?></textarea>
												</td>
											</tr>


											</tbody>
										</table>

										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:40px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center">

											<tbody>
											<tr>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;"><?=lang('refund_payment_mode')?>: </span>

													<span style="font-weight:600;display: inline-block; vertical-align: top;"><?php

                                                        if(isset($OrderData->refund_payment_mode) && $OrderData->refund_payment_mode==1){
                                                            echo lang('store_credit');
                                                        } else if(isset($OrderData->refund_payment_mode) && $OrderData->refund_payment_mode==2){
                                                            echo lang('bank_transfer');
                                                        }else{
                                                            echo "-";
                                                        }

                                                    ?></span>
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
<script type="text/javascript">
      window.onload = function() { window.print(); }
 </script>

	</body>
</html>
