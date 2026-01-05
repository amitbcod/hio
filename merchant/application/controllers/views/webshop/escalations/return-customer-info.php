<div class="barcode-qty-box row order-details-sec-top">
	<div class="col-sm-6 order-id">
		<p><span> Order Number :</span> <?php echo $ReturnOrderData->return_order_increment_id; ?></p>
		<p><span>Purchased on :</span> <?php echo date('d/m/Y',$OrderData->created_at); ?> | <?php echo date('h:i A',$OrderData->created_at); ?></p>
		<p><span>Order Status :</span> <?php echo $this->CommonModel->getReturnOrderStatusLabel($ReturnOrderData->status);?></p>
		
	</div>
	<div class="col-sm-6 order-id">
		<p><span class="huge-name">Customer Name :</span> <?php echo $OrderData->customer_firstname.' '.$OrderData->customer_lastname; ?>  </p>
		<p><span>Shipping Address :</span> <span class="order-address-inner"><?php 

		if(isset($ShippingAddress) && $ShippingAddress->address_id!=''){
			echo $this->WebshopOrdersModel->getFormattedAddress($ShippingAddress);
		}else{
			echo '-';
		}
		?></span></p>
		
	</div>
	
	
	
	
		<div class="col-sm-4 order-id return-request">
		
		<p><span>Return Request Date :</span> <?php echo date('d/m/Y',$ReturnOrderData->created_at); ?> </p>
	
		
		
		</div>
		<div class="col-sm-4 order-id return-request">
			<p><span>Return Request Due Date :</span> <?php echo date('d/m/Y',$ReturnOrderData->return_request_due_date); ?> </p>
		</div>
		
		
		<div class="col-sm-4 order-id return-request">
		<?php if(isset($current_tab) && $current_tab=='refund-request-order'){ ?>
		<p><span>Return Recieved Date </span><?php echo (isset($ReturnOrderData->return_recieved_date) && $ReturnOrderData->return_recieved_date!='' && $ReturnOrderData->return_recieved_date!=0)?date('d/m/Y',$ReturnOrderData->return_recieved_date):''?></p>
		<?php } else{ ?>
			<p><span>Return Recieved Date </span> <input type="text" readonly class="sis-datepicker-ret" name="return_recieved_date" <?php echo ($ReturnOrderData->status==5)?'disabled':''; ?>  id="return_recieved_date" value="<?php echo (isset($ReturnOrderData->return_recieved_date) && $ReturnOrderData->return_recieved_date!='' && $ReturnOrderData->return_recieved_date!=0)?date('d/m/Y',$ReturnOrderData->return_recieved_date):''?>"></p>
		<?php } ?>
		</div>
		
		
		<?php if(isset($current_tab) && $current_tab=='refund-request-order'){ ?>
			<div class="col-sm-4 order-id return-request">
					<p><span>Return Order Total </span> <?php echo $currency_code.' '.number_format($ReturnOrderData->order_grandtotal,2); ?> </p>
				</div>
				
				<div class="col-sm-4 order-id return-request">
					<p><span>Payment Mode </span> <?php echo $OrderPaymentDetail->payment_method_name; ?> </p>
				</div>
				
				<div class="col-sm-4 order-id return-request">
					<p><span>Total Refund Approved </span> <?php echo $currency_code.' '.number_format($ReturnOrderData->order_grandtotal_approved,2); ?> </p>
				</div>
				
				<?php //var_dump($ReturnOrderData).'========'; ?>


<div class="row col-sm-12 mar-top-10">
<?php if($ReturnOrderData->refund_payment_mode==2){ ?>
					<div class="col-sm-10 pad-zero barcode-order-details-entry">
					<h2 class="table-heading-small table-heading-new margin-top-head">Bank Details</h2>
					</div>
					<div class="col-sm-4 order-id return-request">
					<p><span>Bank Name </span> <?php echo (isset($ReturnOrderData->bank_name) && $ReturnOrderData->bank_name!='')?$ReturnOrderData->bank_name. '-'. $ReturnOrderData->bank_branch:'-'; ?> </p>
				</div>
				<div class="col-sm-4 order-id return-request">
					<p><span>Account Number </span> <?php echo (isset($ReturnOrderData->bank_acc_no) && $ReturnOrderData->bank_acc_no!='')?$ReturnOrderData->bank_acc_no:'-'; ?> </p>
				</div>
				<div class="col-sm-4 order-id return-request">
					<p><span>IFSC/IBAN </span> <?php echo (isset($ReturnOrderData->ifsc_iban) && $ReturnOrderData->ifsc_iban!='')?$ReturnOrderData->ifsc_iban:'-'; ?> </p>
				</div>
				
				<div class="col-sm-4 order-id return-request">
					<p><span>BIC/SWIFT </span> <?php echo (isset($ReturnOrderData->bic_swift) && $ReturnOrderData->bic_swift!='')?$ReturnOrderData->bic_swift:'-'; ?> </p>
				</div>
<?php }else if($ReturnOrderData->refund_payment_mode==1){ ?>
	<div class="col-sm-10 pad-zero barcode-order-details-entry">
					<h2 class="table-heading-small table-heading-new margin-top-head">Refund Mode</h2>
					</div>
					<div class="col-sm-4 order-id return-request">
					<p>Store Credit </p>
				</div>
				<?php if(isset($ReturnOrderData->refund_coupon_code) &&  $ReturnOrderData->refund_coupon_code!=''){?>
				<div class="col-sm-4 order-id return-request">
					<p><span>Voucher Code: </span> <?php echo (isset($ReturnOrderData->refund_coupon_code) && $ReturnOrderData->refund_coupon_code!='')?$ReturnOrderData->refund_coupon_code:'-'; ?> </p>
				</div>
				<?php } ?>
<?php } ?>
</div>
		<?php } ?>

		<div class="col-sm-4 order-id return-request">
			<p><span>Refund Status </span> 
					<?php if($ReturnOrderData->refund_status == 1){
						echo '<span class="tracking-complete">Completed</span>';
					}else if($ReturnOrderData->refund_status == 2){
						
						echo '<span class="tracking-incomplete">Rejected</span>';
					}else{
						
						echo '<span class="">Pending</span>';
					}
					?>
			</p>
		</div>
		
		<div class="row col-sm-12 mar-top">
					<div class="col-sm-10 pad-zero barcode-order-details-entry">
					<h2 class="table-heading-small table-heading-new margin-top-head">Customer Return Reason</h2>
						<textarea class="form-control" readonly placeholder=""><?php echo $ReturnOrderData->reason_for_return; ?></textarea>
					</div>
				</div>
		
	
	
</div><!-- barcode-qty-box -->