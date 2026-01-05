<div class="barcode-qty-box row order-details-sec-top">
	<div class="col-sm-6 order-id">
		<?php 
		//print_r($ParentOrder);
			// if($OrderData->parent_id>0){$orderPage='split-order';}else{$orderPage='order';}
			$orderPage='split-order';
		?>
		<p><span> Order Number :</span> <?php echo $EscalationsOrderData->esc_order_id; ?> (Order <a href="<?=base_url()."webshop/".$orderPage."/detail/".$OrderData->order_id?>" target="_blank">#<?=$OrderData->increment_id?></a>)</p>
		<p><span>Purchased on :</span> <?php echo date('d/m/Y',$OrderData->created_at); ?> | <?php echo date('h:i A',$OrderData->created_at); ?></p>
		<!-- <p><span>Order Status :</span> <?php echo $this->CommonModel->getReturnOrderStatusLabel($EscalationsOrderData->refund_status);?></p> -->
		
	</div>
	<?php 
		// $customerId=$OrderData->customer_id;//old
		$customerId=$ParentOrder->customer_id;
		if($customerId > 0){
			$customerNameLink="<a href='".base_url()."CustomerController/customer_details/".$customerId."' target='_blank'>".$OrderData->customer_firstname.' '.$OrderData->customer_lastname."</a>";
		}else{
			//if($OrderData->checkout_method=='guest'){
				$customerNameLink=$OrderData->customer_firstname.' '.$OrderData->customer_lastname.'<br/>('.$OrderData->customer_email.')';
			/*}else{
				$customerNameLink=$OrderData->customer_firstname.' '.$OrderData->customer_lastname;
			}*/
		}
	?>

	<div class="col-sm-6 order-id">
		<p><span class="huge-name">Customer Name :</span> <?php echo $customerNameLink;//$OrderData->customer_firstname.' '.$OrderData->customer_lastname; ?>  </p>
		<p><span>Shipping Address :</span> <span class="order-address-inner"><?php 

		if(isset($ShippingAddress) && $ShippingAddress->address_id!=''){
			echo $this->WebshopOrdersModel->getFormattedAddress($ShippingAddress);
			if($ShippingAddress->mobile_no){
				echo '<br/>Mob: '.$ShippingAddress->mobile_no;
			}
		}else{
			echo '-';
		}
		?></span></p>
		
	</div>

	<div class="col-sm-6 order-id">

	<?php
		// item data
		// $itemsData=$this->WebshopOrdersModel->getMultiDataById('sales_order_items',array('order_id'=>$order_id),'*');
		$productAmount= 0; 
		$productTaxAmount= 0; 
		$product_grand_total=0;
		// print_r($OrderItems);
	  	if(isset($OrderItems) && count($OrderItems)>0){
		   foreach($OrderItems as $items){ 
		   		$qty_ordered=$items->qty_ordered;
		   		$product_discount_percent=$items->tax_percent;
		   		$productPrice=$items->price ;
		   		if($ParentOrder->coupon_code != "" && $items->price > 0.00 && $product_discount_percent > 0.00) {
				  //$productPrice = $items->price - $items->discount_amount;
				  // $productPrice = $items->price - ($items->price*$product_discount_percent)/100;
				  $productTaxAmount+= $items->discount_amount * $qty_ordered;
				}else{
					$productPrice=$items->price;
				}

				$productAmount+= $productPrice * $qty_ordered; 
		   }

		   $product_grand_total= $productAmount - $productTaxAmount;
		}
		$data['product_grand_total']=$product_grand_total;

	?>

		<?php if(isset($productAmount) && $productAmount>0){ //print_r($OrderData);?>
		<p><span> Base Sub Total :</span> <?php echo $currency_code.' '.number_format($productAmount,2);?><br/><span class="incl-tax">( Inclusive of taxes )</span> </p>
		<?php } ?>
		<!-- <?php if(isset($OrderData->base_subtotal) && $OrderData->base_subtotal>0){ ?>
		<p><span> Taxes :</span> <?php echo $currency_code.' '.number_format($OrderData->tax_amount,2);?></p>
		<?php } ?> -->
		<?php if(isset($ParentOrder->coupon_code) && $ParentOrder->coupon_code!=''){ ?>
		<p><span> Discount (<?php echo $ParentOrder->coupon_code; ?>) :</span> - <?php echo $currency_code.' '.number_format($productTaxAmount,2);?></p>
		<?php } ?>
		
		<!-- <?php if(isset($OrderData->shipping_amount) && $OrderData->shipping_amount>0){ ?>
		<p><span> Shipping Charges :</span> + <?php echo $currency_code.' '.number_format($OrderData->shipping_amount,2);?></p>
		<?php } ?>
		<hr/> -->
		
		<?php if(isset($productAmount) && ($productAmount!='' && $productAmount>0) ){ ?>
		<p><span> Sub Total :</span> <?php echo $currency_code.' '.number_format($productAmount -$productTaxAmount,2);?></p>
		<?php }?>

		<?php if(isset($ParentOrder->voucher_code) && ($ParentOrder->voucher_code!='' && $ParentOrder->voucher_amount>0) ){ ?>
		<p><span> Parent Order Voucher ( <?php echo $ParentOrder->voucher_code; ?>) :</span> <?php echo $currency_code.' '.number_format($ParentOrder->voucher_amount,2);?></p>
		<?php }?>

		<!-- <?php if(isset($OrderData->payment_final_charge) && ($OrderData->payment_final_charge!='' && $OrderData->payment_final_charge>0.00) ){ ?>
		<p><span> Payment Charge :</span> + <?php echo $currency_code.' '.number_format($OrderData->payment_final_charge,2);?></p>
		<?php }?> -->
		
		<hr/>
		
			<p class="grand-total"><span>Grand total :</span> <?php echo $currency_code.' '.number_format($product_grand_total,2); ?> </p>
		<?php 
			//} //end parent check
		?>
		
		
		</div>
		<div class="col-sm-6 order-id">
			<p><span>Payment Mode :</span> <?php 
			if($ParentOrder->voucher_code!='' && $ParentOrder->grand_total<=0){
					$payment_method_name='Voucher Payment';
				}else{
					//$payment_method_name=(isset($OrderPaymentDetail) && $OrderPaymentDetail->payment_method_name!='')?$OrderPaymentDetail->payment_method_name:'-';
					
					if(isset($shop_gateway_credentials['display_name']) && $shop_gateway_credentials['display_name']!='') {
						$payment_method_name = $shop_gateway_credentials['display_name'];

					}else if(isset($OrderPaymentDetail) && $OrderPaymentDetail->payment_method_name !=''){
						$payment_method_name = $OrderPaymentDetail->payment_method_name;
					}else{
						$payment_method_name = '-';
					}

					
				}
			
			echo $payment_method_name; ?> </p>
		</div>

	<!-- <?php print_r($OrderData);?> -->
	<!-- cancel refund type -->

	<?php  
		if(empty($EscalationsOrderData->cancel_refund_type) && $EscalationsOrderData->cancel_refund_type==''){
			$this->load->view('webshop/escalations/escalations-split-refund-type.php',$data); 
		}
	?>
	<!-- edn cancel refund type -->
	
	
		<div class="col-sm-4 order-id return-request">
		
		<p><span>Request Escalations Date :</span> <?php echo date('d/m/Y',$EscalationsOrderData->created_at); ?> </p>
	
		
		
		</div>
		
		
		
		<?php if(isset($current_tab) && $current_tab=='escalations-request-order'){ ?>
				<?php if($EscalationsOrderData->cancel_refund_type!=''){ ?>
				<div class="col-sm-4 order-id return-request">
					<?php 
						$refund_type_cancel=$EscalationsOrderData->cancel_refund_type;
						if($refund_type_cancel==0){
							$refund_type_cancel_data='None';
						}

						if($refund_type_cancel==1){
							$refund_type_cancel_data='Store Credit';
						}

						if($refund_type_cancel==2){
							$refund_type_cancel_data='Offline Refund';
						}

						if($refund_type_cancel==3){
							$refund_type_cancel_data='Online Refund';
						}

						if($refund_type_cancel==4){
							$refund_type_cancel_data='Online Refund With Voucher';
						}
					?>
					<p><span>Cancel Refund Type </span> <?php echo $refund_type_cancel_data; ?> </p>
				</div>
				
				<div class="col-sm-4 order-id return-request">
					<!-- <p><span>Total Refund Approved </span> <?php echo $currency_code.' '.number_format($EscalationsOrderData->order_grandtotal_approved,2); ?> </p> -->
					<?php 
						if($refund_type_cancel==4){ 
							?><p><span>Total Refund Approved</span> <?php echo $currency_code.' '.number_format($EscalationsOrderData->order_grandtotal_approved,2); ?> ( Online Refund: <?php echo $OrderData->currency_symbol.($OrderData->grand_total); ?>, Store credit: <?php echo $OrderData->currency_symbol.($EscalationsOrderData->order_grandtotal_approved_online_voucher); ?> )</p>
							<?php 
						}else{
							?><p><span>Total Refund Approved </span> <?php echo $currency_code.' '.number_format($EscalationsOrderData->order_grandtotal_approved,2); ?> </p>
							<?php 
						} 
					?>
				</div>
				
				<?php //var_dump($EscalationsOrderData).'========'; ?>

		<?php  } } ?>

		<div class="col-sm-4 order-id return-request">
			<p><span><b>Request Escalations :</b></span>
					<?php if($EscalationsOrderData->refund_status == 1){
						echo '<span class="tracking-complete"><b>Completed</b></span>';
					}else if($EscalationsOrderData->refund_status == 2){
						
						echo '<span class="tracking-incomplete"><b>Rejected</b></span>';
					}else{
						
						echo '<span class=""><b>Pending</b></span>';
					}
					?>
			</p>
		</div>
		<?php if($EscalationsOrderData->cancel_refund_type==1){ ?>
			<div class="col-sm-10 pad-zero barcode-order-details-entry">
							<h2 class="table-heading-small table-heading-new margin-top-head">Refund Mode</h2>
							</div>
							<div class="col-sm-4 order-id return-request">
							<p>Store Credit </p>
						</div>
						<?php if(isset($EscalationsOrderData->refund_coupon_code) &&  $EscalationsOrderData->refund_coupon_code!=''){?>
						<div class="col-sm-4 order-id return-request">
							<p><span>Voucher Code: </span> <?php echo (isset($EscalationsOrderData->refund_coupon_code) && $EscalationsOrderData->refund_coupon_code!='')?$EscalationsOrderData->refund_coupon_code:'-'; ?> </p>
						</div>
						<?php } ?>
		<?php } ?>
		<div class="row col-sm-12 mar-top">
					<div class="col-sm-10 pad-zero barcode-order-details-entry">
					<h2 class="table-heading-small table-heading-new margin-top-head"><!-- Customer  -->Escalations Reason</h2>
						<textarea class="form-control" readonly placeholder=""><?php echo $EscalationsOrderData->cancel_reason; ?></textarea>
					</div>
				</div>
		
	
	
</div><!-- barcode-qty-box -->