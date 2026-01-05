<?php 
	if(isset($ShipAddress)){$ShipAddress==$ShipAddress;}else{$ShipAddress=array();}
	(new Checkout())->checkoutPriceDetails($CartData,'checkoutPage',$ShipAddress);
