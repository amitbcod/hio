<?php
	$cl=$Response->packages[0]->cl;
	$currencyWebshop=$currencyWebshop;

	$snm=$Response->packages[0]->snm;
	$seller_gst_tin=$Response->packages[0]->seller_gst_tin;
	$client_gst_tin=$Response->packages[0]->client_gst_tin;
	$sadd=$Response->packages[0]->sadd;
	$si=$Response->packages[0]->si;
	$sid=$Response->packages[0]->sid;

	$prd=$Response->packages[0]->prd;
	$rs=$Response->packages[0]->rs;
	$rsTotal=$Response->packages[0]->rs;

	$pt=$Response->packages[0]->pt;
	$cod=$Response->packages[0]->cod;

	$barcode=$Response->packages[0]->barcode;
	$oid_barcode=$Response->packages[0]->oid_barcode;
	$pin=$Response->packages[0]->pin;
	$sort_code=$Response->packages[0]->sort_code;

	$name=$Response->packages[0]->name;
	$address=$Response->packages[0]->address;
	$destination=$Response->packages[0]->destination;

	$returnRadd=$Response->packages[0]->radd;
	$returnRcty=$Response->packages[0]->rcty;
	$returnRst=$Response->packages[0]->rst;
	$returnRpin=$Response->packages[0]->rpin;
	$returnAdd=$returnRadd.','.$returnRcty.','.$returnRst.','.$returnRpin;
	$logo=$Response->packages[0]->delhivery_logo;
	?>
<html>
<head><title>Delivery-slip</title>
<style>
body{font-size: 14px;
    font-family: helvetica;
    color: #000000;padding:10px;}
.table-block{
	border: 1px solid #e0e0e0;
    width: 100%;
    max-width: 765px;
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
</style>
</head>
<body style="background-color:#ffffff; font-family: helvetica;">
<table class="table-block" align="left" bgcolor="FFFFFF" cellspacing="0" cellpadding="0" border="1" width="380" style="width:380px;max-width:380px;background:#fff;font-size:13px;font-family:helvetica;color:#888888; border-color: #e0e0e0;">
 <tbody>
	<tr>
		<td>
			<table width="380"  border="0" cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
				<td style="border-right:1px solid #8c8c8c;text-align: center;padding: 10px; font-size: 14px;width: 50%; word-break: break-word;"><h2 style="color: #394263; margin: 0;padding: 10px 10px;font-family:helvetica;font-weight:600;"><?=$cl?></h2></td>
				<td style="text-align: center;"><img src="<?=$logo?>"></td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td style="text-align: center;padding: 5px 5px 10px;font-family:helvetica;font-weight:normal;color:#000000;">
			<p style="margin-bottom: 15px;"><img src="<?=$barcode?>"></p>
			<p style="text-align: left;font-size: 14px; color: #000; font-weight: 400;"><span style="text-align:left;"><?=$pin?></span> <span style="text-align:right;float: right;font-size: 18px;"><strong><?=$sort_code?></strong></span></p>
		</td>
	</tr>
	<tr>
		<td>
			<table width="380"  cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
					<td style="padding: 5px; width: 70%; border-right: 1px solid #8c8c8c;font-family:helvetica;font-weight:normal;color:#000000;">
						<h4 style="padding: 0;margin: 0;font-size: 14px;">Shipping Address: </h4>
						<h2 style="padding: 0;margin: 5px 0;font-size: 17px;"><?=$name?></h2>
						<p style="font-size: 11px;"><?=$address?></p>
						<p style="font-size: 11px;"><?=$destination?></p>
						<p style="font-size: 13px;"><strong>PIN:<?=$pin?></strong></p>
					</td>
					<td style="padding:5px;font-family:helvetica;font-weight:normal;color:#000000;">
						<h3 style="margin:0;padding:0;font-size:16px;text-align:center;line-height:20px;">
						<?=$pt?><br>
						<?php if ($pt=='COD') { ?>
						<?=$currencyWebshop.' '.number_format($cod)?><br>
						<?php }?>
						Surface
						</h3>
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td>
			<table width="380" cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
					<td width="60%" style="padding:5px;font-size:11px;line-height:14px;border-right: 1px solid #8c8c8c;font-family:helvetica;font-weight:normal;color:#000000;line-height:16px;">
						Seller: <?=$snm?><br>
						Seller GSTIN: <?=$seller_gst_tin?><br>
						Address: <?=$sadd?>
					</td>

					<td style="padding:5px;font-size:11px;line-height:14px;vertical-align:top;font-family:helvetica;font-weight:normal;color:#000000;">
						<p style="font-size:11px;line-height:14px;font-family:helvetica;margin-bottom:0px;">Invoice No: <?=$si?></p>
						<p style="font-size:11px;line-height:14px;font-family:helvetica;">Dt: <?=$sid?></p>
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td>
			<table width="380" border="0" cellpadding="5" cellspacing="0" style="width:100%;font-family:helvetica;font-weight:normal;font-size:12px;color:#000000;">
				<tr>
					<td width="60%" style="text-align:left;border-right:1px solid #8c8c8c; border-bottom: 1px solid #8c8c8c;" >Product</td>
					<td width="20%" style="text-align:center;border-right:1px solid #8c8c8c; border-bottom: 1px solid #8c8c8c;">Price</td>
					<td width="20%" style="text-align:center;border-bottom: 1px solid #8c8c8c;">Total</td>
				</tr>

				<tr>
					<td width="60%" style="text-align:left; padding: 15px 5px;border-right:1px solid #8c8c8c; border-bottom: 1px solid #8c8c8c;"><?=$prd?></td>
					<td width="20%" style="text-align:center; padding: 15px 5px;border-right:1px solid #8c8c8c; border-bottom: 1px solid #8c8c8c;"><?=$currencyWebshop.' '.number_format($rs, 2)?></td>
					<td width="20%" style="text-align:center; padding: 15px 5px; border-bottom: 1px solid #8c8c8c;"><?=$currencyWebshop.' '.number_format($rsTotal, 2)?></td>
				</tr>

				<tr>
					<td width="60%" style="text-align:left;border-right:1px solid #8c8c8c;"><strong>Total</strong></td>
					<td width="20%" style="text-align:center;border-right:1px solid #8c8c8c;"><strong><?=$currencyWebshop.' '.number_format($rs, 2)?></strong></td>
					<td width="20%" style="text-align:center;"><strong><?=$currencyWebshop.' '.number_format($rsTotal, 2)?></strong></td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td style="padding:5px; text-align:center;">
			<p><img src="<?=$oid_barcode?>"></p>
		</td>
	</tr>

	<tr>
		<td style="padding:5px;font-size:11px;line-height:14px;font-family:helvetica;font-weight:normal;color:#000000;">
			<p style="margin:0;font-size:11px;line-height:14px;font-family:helvetica;font-weight:normal;color:#000000;">Return Address: <?=$returnAdd?></p>
		</td>
	</tr>
 </tbody>
 </table>
</body>
</html>
