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

			
			
			// print_r($total_grand_);die;


			
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
	<?php
	 $deliveryAttempts = $CI->B2BOrdersModel->getMultiDataById('b2b_orders_delivery_details', array('order_id' => $OrderData->order_id), '', 'id', 'ASC');
	 //echo "<pre>";
	 //print_r($deliveryData);
	 //exit;
	 ?>
	<div class="col-sm-4 order-id">
		<!-- <p><span>Payment Mode :</span> Offline </p> -->
		<p><span>Customer Payment Mode :</span> 
			<?php  
			if($order_payment_method->payment_method == "Cheque_FundsTransfer"){
			?>
			Cheque/Funds Transfer
			<?php }else {?>
				Cc Avenue
			<?php  }?>
		</p>
		<!-- <p class="position-relative"><textarea placeholder="Note" readonly class="form-control col-sm-10 "><?= $sales_order->internal_notes ?></textarea> -->
<?php if (!empty($deliveryAttempts)) : ?>
    <?php 
        // Get the latest attempt (last row)
        $lastAttempt = end($deliveryAttempts);
    ?>
    <table class="table table-bordered deliverytable">
        <thead>
            <tr>
                <th>Attempt No</br></th>
				<th>Delivery Person</br></th>
                <th>Delivery </br> Date</th>
                <!-- <th>Driver ID</th> -->
                <th>Status</th>
                <th>Remarks</th>
                <th>Success/Reason for Failure</th>
				<?php if($OrderData->shipment_type != 2) { ?>
                <th>Action</th>
				<?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($deliveryAttempts as $attempt) : ?>
                <tr>
                    <td><?= $attempt->delivery_attempt_no; ?></td>
					<td><?= $attempt->own_delivery_person_name; ?></td>
                    <td><?= date('d M Y', strtotime($attempt->delivery_date)); ?></td>
                    <!-- <td><?= $attempt->driver_id; ?></td> -->
                    <td>
                        <?php
                        switch ($attempt->delivery_status) {
                            case 1: echo "Shipped"; break;
                            case 2: echo "Failed Attempt 1"; break;
                            case 3: echo "Attempt 2"; break;
                            case 4: echo "Attempt 2 Failed"; break;
                            case 5: echo "Attempt 3"; break;
                            case 6: echo "Attempt 3 Failed"; break;
                            case 7: echo "Collect From Store"; break;
                            case 8: echo "Delivered"; break;
                            default: echo "Pending"; break;
                        }
                        ?>
                    </td>
                    <td><?= !empty($attempt->remarks) ? $attempt->remarks : '-'; ?></td>
                    <td><?= !empty($attempt->reason_for_attempt_failed) ? $attempt->reason_for_attempt_failed : '-'; ?></td>
					<?php if($OrderData->shipment_type != 2) { ?>
                    <td>
					<?php if ($attempt->id == $lastAttempt->id && $attempt->delivery_status != 8) : ?>
						<?php if (empty($attempt->reason_for_attempt_failed)) : ?>
							<!-- Latest attempt not failed → Show Mark as Failed -->
							<button class="btn btn-danger btn-sm" 
									onclick="MarkAsFailedPopup('<?= $attempt->order_id ?>', '<?= $attempt->delivery_attempt_no ?>')">
								Mark as Failed
							</button>
						<?php elseif ($attempt->delivery_attempt_no < 2): ?>
							<!-- Latest attempt failed & attempt no < 3 → Show Assign New Delivery -->
							<button class="btn btn-primary btn-sm" 
									onclick="AssignNewDeliveryPopup('<?= $attempt->order_id ?>', '<?= $attempt->delivery_attempt_no ?>')">
								<?php echo "Attempt ".$attempt->delivery_attempt_no+1; ?>
							</button>
						<?php else: ?>
							<!-- All attempts used or nothing to assign -->
							<span class="text-muted">-</span>
						<?php endif; ?>
					<?php else: ?>
						<!-- Not the latest attempt -->
						<span class="text-muted">-</span>
					<?php endif; ?>
				</td>
				<?php } ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>

<?php endif; ?>





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


<script>




	function submitMarkFailedForm() {
    var form = $('#mark_failed_form');
    $.ajax({
        url: BASE_URL + "B2BOrdersController/MarkAsFailed",
        type: "POST",
        data: form.serialize(),
        success: function(response) {
            $('#modal').modal('hide');
            var res = jQuery.parseJSON(response);

            swal({
                title: res.status == 200 ? "Success" : "Error",
                icon: res.status == 200 ? "success" : "error",
                text: res.message,
                buttons: true,
            }, function() {
                location.reload();
            });
        }
    });
    return false;
}

function MarkAsFailedPopup(order_id, attempt_no) {
    if (order_id != '') {
        $.ajax({
            url: BASE_URL + "B2BOrdersController/MarkAsFailedPopup",
            type: "POST",
            data: {
                order_id: order_id,
                attempt_no: attempt_no
            },
            success: function(response) {
                if (response != 'error') {
                    $("#FBCUserCommonModal").modal();
                    $("#modal-content").html(response);
                } else {
                    swal("Error", "Something went wrong.", "error");
                }
            }
        });
    } else {
        return false;
    }
}

</script>