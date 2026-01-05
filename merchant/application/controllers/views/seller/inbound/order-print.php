<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="generator" content="Jekyll v4.1.1">
    <title> Order print label</title>
	


    <!-- Bootstrap core CSS -->
    <link href="<?php echo SKIN_CSS; ?>bootstrap.min.css" rel="stylesheet">

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
	i.float-right.fa.fa-fw.fa-sort {
    margin-top: 4px;
}
    </style>
    

    </head>
	<body style="margin:0; padding:0;vertical-align:top; font-family: 'Montserrat', sans-serif; font-size: 14px; background:#fff; font-weight:400;color:#444444;" data-new-gr-c-s-check-loaded="14.996.0" data-gr-ext-installed="">
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
														<img src="<?php echo base_url(); ?>public/images/barcode.png" width="132">
												</td>
											</tr>
											</tbody>
										</table>
										
										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:40px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center">
										
											<tbody>
											<tr>
												<td style="padding:0;vertical-align: middle; text-align: left;" width="100%">
													<h1 style="font-weight: 600;font-size: 18px;line-height: 22px;letter-spacing: 0.05em;text-transform: capitalize;color: #444444;font-family: 'Montserrat', sans-serif;">Order Details</h1>	
												</td>
											</tr>
											
											
											</tbody>
										</table>
										
										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:40px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center">
										
											<tbody>
											<tr>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;">Order Number</span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php echo $inboundData['id']; ?></span>
												</td>
												<td width="10%"></td>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;">Customer Name</span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php echo $inboundData['name'];?></span>
												</td>
												
											</tr>

                                            <tr>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;">Total Products</span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php echo $inboundData['total_products']; ?></span>
												</td>
												<td width="10%"></td>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;">Total Price</span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php echo $inboundData['total_price'];?></span>
												</td>
												
											</tr>

											<tr>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													
												</td>
												<td width="10%"></td>
												<td style="padding:0;vertical-align: top; text-align: left;font-weight: 500;font-size: 14px;line-height: 17px;text-transform:capitalize;color: #333333;font-family: 'Montserrat', sans-serif;padding-bottom:30px;" width="45%">
													<span style="display: inline-block;width: 170px; vertical-align: top;">Purchased on</span> <span style="font-weight:600;display: inline-block; vertical-align: top;"><?php echo date('d/m/Y',$inboundData['updated_at']); ?> | <?php echo date('h:i A',$inboundData['updated_at']); ?></span>
												</td>
											</tr>
											</tbody>
										</table>
										
									
										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:60px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center">						
											<tbody>
											<tr>
												<td style="padding:0;vertical-align: middle; text-align: left;" width="100%">
													<h1 style="font-weight: 600;font-size: 18px;line-height: 22px;letter-spacing: 0.05em;text-transform: capitalize;color: #444444;font-family: 'Montserrat', sans-serif;">Products Details</h1>	
												</td>
											</tr>								
											</tbody>
										</table>
										
										<table cellpadding="0" cellspacing="0" style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:10px auto 100px;text-align:left;padding: 0 0px;border:1px solid #dee2e6;border-collapse: collapse;" width="100%" align="center" class="table-style">
										
											<thead style="background: linear-gradient(90deg, rgb(203, 31, 83) 0%, rgb(115, 16, 91) 84.41%);color: rgb(255, 255, 255);">
											<tr>
											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">INBOUND ID</th>
                                              <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">SKU</th>
											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">Product Name</th>
											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">Variant</th>											
											  <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">Qty Ordered </th>

                                              <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">Price <i class="float-right fa fa-fw fa-sort"></i></th>

                                              <th style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 600;text-transform: uppercase;white-space: nowrap;border-bottom: 2px solid #dee2e6;font-size: 14px;color:#ffffff;text-align:center;">Total Price </th>
											</tr>
										  </thead>
											
											<tbody>
											
											<?php

											if(isset($inboundProducts) && count($inboundProducts)>0){
											  foreach($inboundProducts as $item){
												  
												$variants_html='';
                                                $product_variants=$item['variants'];
                                                if(isset($product_variants) && $product_variants!=''){
                                                    $variants=json_decode($product_variants, true);
                                                    if(isset($variants) && count($variants)>0){
                                                        
                                                        
                                                        foreach($variants as $pk=>$single_variant){
                                                            foreach($single_variant as $key=>$val){
                                                                
                                                            $variants_html.='<span class="variant-item">'.$key.' - '.$val.'</span><br>';
                                    
                                                            }
                                                        }
                                                    }
                                                }else{
                                                    $variants_html='-';  
                                                }
												 
												  ?>
											  <tr id="oi-single-">
											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo $item['inbound_no']; ?></td>
                                              <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo $item['sku']; ?></td>
											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo $item['product_name']; ?></td>
											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo $variants_html; ?></td>
											  <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo $item['qty_scanned']; ?></td>
                                              <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo $item['price']; ?></td>
                                              <td style="border: 1px solid #dee2e6;padding: .75rem;vertical-align: middle;font-weight: 500;    color: #212529;font-size: 14px;text-align:center;"><?php echo $item['total_price']; ?></td>
										
											</tr>
										  <?php }  
										  } ?>
											
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
