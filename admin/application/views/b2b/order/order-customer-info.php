<?php
// Get CI instance
$CI =& get_instance();

// Load models (only if not autoloaded already)
$CI->load->model('ShopProductModel');
$CI->load->model('CommonModel');
$CI->load->model('B2BOrdersModel');
$CI->load->model('WebshopOrdersModel');
// echo "<pre>";
// print_r($OrderData);die;

$ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->webshop_order_id, 'address_type' => 2), '');
?>
<div class="barcode-qty-box row order-details-sec-top">
	<div class="col-sm-6 order-id">
		<?php
		// echo phpversion();
		// die();
		$sales_order = $CI->ShopProductModel->getSingleDataByID('sales_order', array('order_id' => $OrderData->webshop_order_id), '');
		
		$order_payment_method = $CI->ShopProductModel->getSingleDataByID('sales_order_payment', array('order_id' => $OrderData->webshop_order_id), '');
		// echo "<pre>"; print_R($order_payment_method);die();
		?>
		<p><span>Order Number :</span> <?php echo $OrderData->increment_id; ?></p>
		<?php
		if ($OrderData->main_parent_id > 0 || $OrderData->parent_id > 0) {
			$purchaseOn = $CI->CommonModel->getSingleShopDataByID('b2b_orders', array('order_id' => $OrderData->main_parent_id, 'order_id' => $OrderData->main_parent_id), 'created_at');
			if (isset($purchaseOn) && $purchaseOn->created_at) {
				$purchaseOnDate = date('d/m/Y', $purchaseOn->created_at) . ' | ' . date('h:i A', $purchaseOn->created_at);
			}
		} else {
			$purchaseOnDate = date('d/m/Y', $OrderData->created_at) . ' | ' . date('h:i A', $OrderData->created_at);
		}
		?>
		<p><span>Purchased on :</span> <?php echo $purchaseOnDate; ?></p>
		<p><span>Order Status :</span> 
		
			<?php echo $CI->CommonModel->getOrderStatusLabel($OrderData->status); ?>
				
		</p>
		
			<p><span>Shipping Address :</span> 	<span class="order-address-inner"><?php

		if (isset($ShippingAddress) && $ShippingAddress->address_id != '') {
			$shipName = $ShippingAddress->first_name . ' ' . $ShippingAddress->last_name;
			$shipMobile = $ShippingAddress->mobile_no;
			if ($shipName) {
				//echo '<br><br>';
				echo $shipName . '<br/>';
			}
			echo $this->WebshopOrdersModel->getFormattedAddress($ShippingAddress);
			if ($shipMobile) {
				echo '<br/>Mob:';
				echo $shipMobile;
			}
		} else {
			echo '-';
		}
		if (isset($ShippingAddress) && $ShippingAddress->company_name != '') {
			echo '<br/>Comp Name:';
			echo $ShippingAddress->company_name;
		}
		?></span></p>
		
	
	</div>
	<div class="col-sm-6 order-id">
		<p><span class="huge-name">Customer Name :</span> <?php echo $OrderData->customer_firstname . ' ' . $OrderData->customer_lastname // $CI->B2BOrdersModel->getOrderCustomerNameByOrderId($OrderData->order_id);
			?> </p>
		<?php
	
		$B2b_items = $CI->ShopProductModel->getSingleDataByID('b2b_order_items', array('order_id' => $OrderData->order_id), '');
		$getCategory = $CI->B2BOrdersModel->getCategory($B2b_items->parent_product_id);
		$product_data = [];
		$new_product_data=[];

		if (isset($OrderItems) && count($OrderItems) > 0) {
			foreach ($OrderItems as $item) {
				$product_details = $CI->ShopProductModel->getSingleDataByID('products', array('id' => $item->parent_product_id), '');
				$new_product_details = $CI->ShopProductModel->getSingleDataByID('products', array('id' => $item->product_id), '');
				$product_data[] = $product_details;
				$new_product_data[] = $new_product_details;

			}
		}
		// echo "<pre>";
		// 	print_r($new_product_data);die;

		foreach ($product_data as $keyprod => $valprod) {
			$product_name = $valprod->name ?? null;
		}
		$total_webshop_price = 0;

		foreach ($new_product_data as $keyprod => $valprod) {
			if (isset($valprod->webshop_price)) {
				$total_webshop_price += (float)$valprod->webshop_price;
			}
		}
		// print_r($webshop_price);die;
		
		$publisherdetails = $CI->B2BOrdersModel->getPublisherDetails($OrderData->publisher_id);
		$publication_name = '';
		
		$publication_name = $PublisherDetails->publication_name ?? null;
		if ($publication_name === 'Amar Chitra Katha (Books)') {
			$order_total=$total_webshop_price;
		} elseif($getCategory){
			$order_total=$total_webshop_price;
		}else{
			$order_total=$OrderData->subtotal;
		}
		// print_r($order_total);die;
		if($OrderData->order_id == '1895' ||  $OrderData->order_id == '1732' || $OrderData->order_id == '1739' || $OrderData->order_id == '1737' || $OrderData->order_id == '1735' || $OrderData->order_id == '1745' || $OrderData->order_id == '1682' || $OrderData->order_id == '1979' || $OrderData->order_id == '1999' || $OrderData->order_id == '2007' || $OrderData->order_id == '2008' || $OrderData->order_id == '1805' || $OrderData->order_id == '1777' || $OrderData->order_id == '1780' || $OrderData->order_id == '1794'){ 
			$order_total=$OrderData->subtotal + ($OrderData->shipping_amount * $OrderData->total_qty_ordered);
		}
		if($OrderData->order_id == '3137'){
			$order_total=$OrderData->subtotal;
		}

		if ($product_name === 'Business Manager Magazine' || $product_name === 'Business Manager Magazine Digital') {
			// echo "hi2";
			$order_total=$OrderData->subtotal - $OrderData->shipping_amount ;
			// print_r($whuso_income);
		}
		// print_r($OrderData->subtotal);
		// die;
		$webshopurl = '';
		if ($OrderData->publisher_id) {
			$webshopurl = base_url() . "b2b/customer/detail/" . $OrderData->publisher_id;
		}
		?>
		<p><span class="huge-name">Merchant Name :</span> <a href="<?= $webshopurl ?>" target="_blank"><?php echo $CI->CommonModel->getWebShopNameByShopId($OrderData->publisher_id); ?> </a> </p>
		<p><span class="huge-name">Shipment :</span> <?php echo $CI->CommonModel->getOrderShipmentLabel($OrderData->shipment_type); ?> </p>
	</div>

	<div class="col-sm-4 order-id">
		<?php if (isset($OrderData->parent_id) && $OrderData->parent_id > 0) { ?>
			<p><span>B2B order total :</span> <?php echo $currency_code . ' ' . number_format($ParentOrder->subtotal, 2); ?> </p>
			<p><span> Discount (<?php echo $ParentOrder->discount_percent; ?>%) :</span> <?php echo $currency_code . ' ' . number_format($ParentOrder->discount_amount, 2); ?></p>
			<p><span>B2B Taxes amount :</span> <?php echo $currency_code . ' ' . number_format($ParentOrder->tax_amount, 2); ?></p>
			<p><span>B2B Net Payable Amount :</span> <?php echo $currency_code . ' ' . number_format($ParentOrder->grand_total, 2); ?></p>
		<?php } else { ?>
			<p><span>B2B order total :</span> <?php echo $currency_code . ' ' . number_format($order_total, 2); ?> </p>
			<p><span> Discount (<?php echo $OrderData->discount_percent; ?>%) :</span> <?php echo $currency_code . ' ' . number_format($OrderData->discount_amount, 2); ?></p>
			<p><span>B2B Taxes amount :</span> <?php echo $currency_code . ' ' . number_format($OrderData->tax_amount, 2); ?></p>
			<?php
			// echo "<pre>";
			// print_r($OrderData);die;
			$product_data = [];
			$new_product_data=[];
			if (isset($OrderItems) && count($OrderItems) > 0) {
				foreach ($OrderItems as $item) {
					$product_details = $CI->ShopProductModel->getSingleDataByID('products', array('id' => $item->parent_product_id), '');
					$new_product_details = $CI->ShopProductModel->getSingleDataByID('products', array('id' => $item->product_id), '');
					$product_data[] = $product_details;
					$new_product_data[] = $new_product_details;
				}
			}
			// $shipping_charge = 0;
			// echo "<pre>";
			// print_r($product_data);
			// die;

			$B2b_items = $CI->ShopProductModel->getSingleDataByID('b2b_order_items', array('order_id' => $OrderData->order_id), '');
			$getCategory = $CI->B2BOrdersModel->getCategory($B2b_items->parent_product_id);
			
			// $getCategory = $CI->getCategory($item['parent_product_id']);
			// echo "<pre>";
			// print_r($getCategory);
			// die;
			

			
			foreach ($product_data as $keyprod => $valprod) {
				$product_name = $valprod->name ?? null;
			}
			$total_webshop_price = 0;
			foreach ($new_product_data as $keyprod => $valprod) {
				if (isset($valprod->webshop_price)) {
					$total_webshop_price += (float)$valprod->webshop_price;
				}
			}
			// $shipping_charge = $OrderData->shipping_amount;  // commented on 23-09-24
			// $shipping_charge = $OrderData->shipping_amount  * $B2b_items->qty_ordered; // written on 23-09-24
			// print_r($shipping_charge);
			// die;

			$shipping_charge = $OrderData->shipping_amount; // written on 03-10-24
			$pub_ids = $CI->CommonModel->getShopsForBTwoBOrders($OrderData->webshop_order_id);
			// print_r($pub_ids);
			// echo $OrderData->publisher_id;
			$publisher_commision_per = $CI->CommonModel->getWebShopCommisionByShopId($OrderData->publisher_id);
			$publisher_commision_per;
			// $total_grand_ = $OrderData->grand_total - $shipping_charge;
			// $whuso_income = (($publisher_commision_per / 100) * ($total_grand_));
			// $Payable_Amount = ($total_grand_ - $whuso_income) + $shipping_charge;

			$publisherdetails = $CI->B2BOrdersModel->getPublisherDetails($OrderData->publisher_id);
			$publication_name = '';

			$publication_name = $PublisherDetails->publication_name ?? null;
			if ($publication_name === 'Amar Chitra Katha (Books)') {
				$total_grand_=$total_webshop_price;
			} elseif($getCategory){
				$total_grand_ = $total_webshop_price;
			}else{
				$total_grand_ = $OrderData->grand_total - ($OrderData->shipping_amount);
			}
			// $total_grand_ = $OrderData->grand_total - $shipping_charge;

			// $total_grand_ = ($OrderData->grand_total+$shipping_charge) - $shipping_charge;

			// print_r($total_grand_);
			// die;

			if ($publication_name === 'The Institute of Cost Accountants of India ') {
				$publisher_commision_per = 0;
				$whuso_income = 100 * $OrderData->total_qty_ordered;
			} else {
				$whuso_income = (($publisher_commision_per / 100) * ($total_grand_));
			}


			if ($product_name === 'Financial Times Asia (Print Edition)') {
				$publisher_commision_per = 10;
				$whuso_income = (($publisher_commision_per / 100) * ($total_grand_));
			}

			// print_r($whuso_income);

			if ($product_name === 'Financial Times Weekend Newspaper with Magazine') {
				// echo "hi2";
				$publisher_commision_per = 10;
				$whuso_income = (($publisher_commision_per / 100) * ($OrderData->grand_total));
				// print_r($whuso_income);
			}

			if ($product_name === 'Business Manager Magazine' || $product_name === 'Business Manager Magazine Digital') {
				// echo "hi2";
				$total_grand_=$OrderData->subtotal - $OrderData->shipping_amount ;
				// print_r($whuso_income);
			}
            if ($product_name === 'Down To Earth Magazine' || $product_name === 'Down To Earth Hindi Magazine') {
				// echo "hi2";
				$total_grand_ = $OrderData->grand_total;
				$whuso_income = (($publisher_commision_per / 100) * ($OrderData->grand_total));
				// print_r($whuso_income);
			}
			if($OrderData->order_id == '3137'){
				$shipping_charge=0;
			}

			if ($OrderData->order_id == '1471') {
				$publisher_commision_per = 0;
				$whuso_income = 4340;
			}
			if($OrderData->order_id == '1726'){
				
				$publisher_commision_per = 0;
				$whuso_income = 1230;
			}
			// print_r($total_grand_);die;

			if($OrderData->order_id == '1895' ||  $OrderData->order_id == '1732' || $OrderData->order_id == '1739' || $OrderData->order_id == '1737' || $OrderData->order_id == '1735' || $OrderData->order_id == '1745' || $OrderData->order_id == '1682' || $OrderData->order_id == '1979' || $OrderData->order_id == '1999' || $OrderData->order_id == '2007' || $OrderData->order_id == '2008' || $OrderData->order_id == '1805' || $OrderData->order_id == '1780' || $OrderData->order_id == '1794'){
				$total_grand_ = $OrderData->grand_total;
				$whuso_income = (($publisher_commision_per / 100) * ($OrderData->grand_total));
			}

			if( $OrderData->order_id == '1777'){
				$shipping_charge = 1600;
				$total_grand_ = $OrderData->grand_total;
				$whuso_income = (($publisher_commision_per / 100) * ($OrderData->grand_total));
			}
			
			$Payable_Amount = ($total_grand_ - $whuso_income) + $shipping_charge;
			// print_r($Payable_Amount);
			// die;

			?>
			<p><span>Shipping Amount :</span> <?php echo $currency_code . ' ' . number_format($shipping_charge, 2); ?></p>
			<?php if (isset($OrderData->payment_gateway_charges)) { ?>
				<p><span> Payment gateway charges :</span> <?php echo $currency_code . ' ' . number_format($OrderData->payment_gateway_charges, 2); ?></p>
			<?php } ?>
			<p><span>Merchant Commision :</span> <?php echo $currency_code . ' ' . number_format($publisher_commision_per, 2) . '%'; ?></p>
			<!-- <p><span>Fixed Commision :</span> <?php echo $currency_code . ' ' . number_format($OrderData->whuso_income, 2); ?></p> -->
			<p><span>Yellow Market income :</span> <?php echo $currency_code . ' ' . number_format($whuso_income, 2); ?></p>
			<p><span>B2B Net Payable Amount :</span> <?php echo $currency_code . ' ' . number_format($Payable_Amount, 2); ?></p>
		<?php } ?>
	</div>
	<div class="col-sm-4 order-id">
		<p><span>Payment Mode :</span> Offline </p>
		<p><span>Customer Payment Mode :</span> 
			<?php  
			if($order_payment_method->payment_method == "Cheque_FundsTransfer"){
			?>
			Cheque/Funds Transfer
			<?php }else {?>
				Cc Avenue
			<?php  }?>
		</p>
		<p class="position-relative"><textarea placeholder="Note" readonly class="form-control col-sm-10 "><?= $sales_order->internal_notes ?></textarea>
	</div>
	

	<?php if (isset($OrderData->parent_id) && $OrderData->parent_id > 0) { ?>
		<div class="col-sm-4 order-id">
			<p><span>Split order total </span> <?php echo $currency_code . ' ' . number_format($OrderData->subtotal, 2); ?> </p>
			<p><span> Discount (<?php echo $OrderData->discount_percent; ?>%) :</span> <?php echo $currency_code . ' ' . number_format($OrderData->discount_amount, 2); ?></p>
			<p><span>B2B Taxes amount :</span> <?php echo $currency_code . ' ' . number_format($OrderData->tax_amount, 2); ?></p>
			<p><span>B2B Net Payable Amount For Split Order :</span> <?php echo $currency_code . ' ' . number_format($OrderData->grand_total, 2); ?></p>
		</div>
	<?php } ?>
</div><!-- barcode-qty-box -->
