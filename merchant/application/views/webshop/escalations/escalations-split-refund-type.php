<?php //print_r($product_grand_total);
	//if($OrderData->parent_id > 0 || $OrderData->main_parent_id > 0){

		/*start*/
			$productAmount_ref= 0; 
			$productTaxAmount_ref= 0; 
			$product_grand_total_ref=0;
			$refundAmount=0;
			// print_r($OrderItems);
		  	if(isset($OrderItems) && count($OrderItems)>0){
			   foreach($OrderItems as $itemr){ 
			   		$qty_ordered_ref=$itemr->qty_ordered;
			   		$product_discount_percent_ref=$itemr->tax_percent;
			   		$productPrice_ref=$itemr->price ;
			   		if($ParentOrder->coupon_code != "" && $itemr->price > 0.00 && $product_discount_percent_ref > 0.00) {
					  //$productPrice = $items->price - $items->discount_amount;
					  // $productPrice = $items->price - ($items->price*$product_discount_percent)/100;
					  $productTaxAmount_ref+= $itemr->discount_amount * $qty_ordered_ref;
					}else{
						$productPrice_ref=$itemr->price;
					}
					$productAmount_ref+= $productPrice_ref * $qty_ordered_ref; 
			   }
			   $product_grand_total_ref= $productAmount_ref - $productTaxAmount_ref;
			}

		/*end*/
		
		$order_grandtotal_approved = $product_grand_total_ref;
		$voucherAmount=$ParentOrder->voucher_amount;
		if(isset($OrderPaymentDetail->payment_method) && !empty($OrderPaymentDetail->payment_method)){
			$order_grandtotal_approved = 0;
			//echo $OrderPaymentDetail->payment_method;
			$payment_method_cancel=$OrderPaymentDetail->payment_method;
			$order_grandtotal_approved = $product_grand_total_ref;
			// $order_grandtotal_approved = $OrderData->subtotal + $OrderData->payment_final_charge;
			if($payment_method_cancel == 'via_transfer' || $payment_method_cancel == 'cod' ) {
					if($ParentOrder->voucher_code != '' && $ParentOrder->voucher_amount > 0.00 ) {
						$order_grandtotal_approved = $ParentOrder->voucher_amount;
						if($order_grandtotal_approved > 0 ){
							$none_cancel='';
							$store_cancel='checked';
							$offline_cancel='';
						}
					}else{
						if($payment_method_cancel == 'via_transfer') {
							//$order_grandtotal_approved = $order_grandtotal_approved;;
							$order_grandtotal_approved = 0;
						/*}else{
							$order_grandtotal_approved = 0;*/
						}
					}
			}
		}

		
?>
<div class="col-sm-4 order-id return-request">
	<p><span>Total Refund Amount </span> <span class="totalRefundAmount"><?php echo $currency_code.' '.number_format($order_grandtotal_approved,2); ?> </span></p>
</div>
<div class="col-sm-10 pad-zero barcode-order-details-entry">
	<h2 class="table-heading-small table-heading-new margin-top-head"><b>Refund Mode</b></h2>
</div>
<div class="col-sm-12 order-id refund-mode-radio">
	<div class="checkbox-group">
		<span id="errorMsg"></span>
		<div class="radio">       
            <label><input type="radio" name="cancel_refund_type" id="cancel_refund_type" value="1" id="" <?php if(isset($store_cancel)){echo $store_cancel;} ?>>Store Credit <span class="checkmark"></span></label>
        </div> 
        <div class="radio">       
            <label><input type="radio" name="cancel_refund_type" id="cancel_refund_type" value="2" id="" <?php if(isset($offline_cancel)){echo $offline_cancel;} ?>>Offline Refund<span class="checkmark"></span></label>
        </div> 
        <div class="radio">       
            <label><input type="radio" name="cancel_refund_type"  id="cancel_refund_type" value="0" id="" <?php if(isset($none_cancel)){echo $none_cancel;} ?>>None <span class="checkmark"></span></label>
        </div> 

        <?php 
        //$order_grandtotal_approved_stripe_voucher=0;
		$order_grandtotal_approved_online=0;
        if ($strip_cancel_enable=="yes" && $payment_method_cancel=="stripe_payment") {
        	
			 if($ParentOrder->grand_total_original >= $product_grand_total_ref){
	        		$order_grandtotal_approved=$product_grand_total_ref;
	        		
					$order_grandtotal_approved_online= $order_grandtotal_approved;
				?>
		        	<div class="radio">      
			            <label><input type="radio" name="cancel_refund_type" id="cancel_refund_type" value="3" id="" <?php if(isset($strip_cancel)){echo $strip_cancel;} ?>>Online Refund<span class="checkmark"></span></label>
			        </div>
			    <?php
			}else{

				
				$parent_order_grandtotal=$ParentOrder->grand_total_original;
				$order_grandtotal_approved= $product_grand_total_ref - $parent_order_grandtotal;
				$order_grandtotal_approved_online= $parent_order_grandtotal;
				$refundAmount=$order_grandtotal_approved;
				?>
		        	<div class="radio">      
			            <label><input type="radio" name="cancel_refund_type" id="cancel_refund_type" value="4" id="" <?php if(isset($strip_cancel)){echo $strip_cancel;} ?>>Online Refund ( Online Refund: <?php echo $ParentOrder->currency_symbol.($parent_order_grandtotal); ?>, Store credit: <?php echo $ParentOrder->currency_symbol.($order_grandtotal_approved); ?> )<span class="checkmark"></span></label>
			        </div>
			    <?php			
			}
            
        }

        if ($strip_cancel_enable=="yes" && $payment_method_cancel=="paypal_express") {
        	
			  if($ParentOrder->grand_total_original >= $product_grand_total_ref){
	        		$order_grandtotal_approved=$product_grand_total_ref;
					$order_grandtotal_approved_online= $order_grandtotal_approved;
				?>
		        	<div class="radio">      
			            <label><input type="radio" name="cancel_refund_type" id="cancel_refund_type" value="3" id="" <?php if(isset($online_cancel)){echo $online_cancel;} ?>>Online Refund<span class="checkmark"></span></label>
			        </div>
			    <?php
			}else{
				$parent_order_grandtotal=$ParentOrder->grand_total_original;
				$order_grandtotal_approved= $product_grand_total_ref - $parent_order_grandtotal;
				$order_grandtotal_approved_online= $parent_order_grandtotal;
				$refundAmount=$order_grandtotal_approved;
				?>
		        	<div class="radio">      
			            <label><input type="radio" name="cancel_refund_type" id="cancel_refund_type" value="4" id="" <?php if(isset($online_cancel)){echo $online_cancel;} ?>>Online Refund ( Online Refund: <?php echo $ParentOrder->currency_symbol.($parent_order_grandtotal); ?>, Store credit: <?php echo $ParentOrder->currency_symbol.($order_grandtotal_approved); ?> )<span class="checkmark"></span></label>
			        </div>
			    <?php			
			}
            
        }
    ?>

	</div>
	<?php 
		if(isset($OrderPaymentDetail->payment_method) && !empty($OrderPaymentDetail->payment_method)){
			if($payment_method_cancel == 'via_transfer') {
			if($payment_method_cancel == 'via_transfer' && $voucherAmount > 0.00) {
	?>
		<div class="checkbox_refund">
			<input type="checkbox" name="cheque_value" id="cheque_value" class="cheque_value" value="1"> Cheque Already Recived
		</div>

	<?php  } } } ?>

	<div class="save-discard-btn pad-bottom-20 return-req-bottom">
		<!-- <div class="refund_value"> -->
			<input type="hidden" id="sales_order_escalations_id"  name="sales_order_escalations_id" value="<?php echo $EscalationsOrderData->id; ?>"  >	
		<!-- </div> -->
		<input type="text" id="order_grandtotal_approved" class="refund_value" name="order_grandtotal_approved" value="<?php echo $order_grandtotal_approved; ?>" <?php if(isset($none_cancel) && $order_grandtotal_approved==0){echo 'readonly';} ?>>	
		<?php 		
			if($EscalationsOrderData->refund_status==0){ ?> 
			<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/refunds/write',$this->session->userdata('userPermission'))){ ?>
			  <button class="purple-btn" id="confirm-returns-btn-mode"  onclick="OpenConfirmEscalationsRefundMode();">Confirm Refund Mode</button>
			<?php } ?>
		<?php } ?>
	</div>
	
</div>
<script type="text/javascript">
	if ($("input[name='cancel_refund_type']:checked").val()==3) {
		$('#order_grandtotal_approved').prop('readonly', true);

	}

	function OpenConfirmEscalationsRefundMode(){

		$('#confirm-returns-btn-mode').attr("disabled");
		var cancel_refund_type = $("input[name='cancel_refund_type']:checked").val();
		if(cancel_refund_type){
			$('#errorMsg').html('');
			// swal('Success','obj.message','success');
			var voucher_amount=0;
			var order_grandtotal_approved_online=0;
			var escalationsId=$("#sales_order_escalations_id").val();
			var order_grandtotal_approved=$("#order_grandtotal_approved").val();
			if(cancel_refund_type==4){
				voucher_amount=$("#order_grandtotal_approved").val();
				order_grandtotal_approved_online='<?=number_format($order_grandtotal_approved_online,2)?>';
				order_grandtotal_approved=parseFloat(voucher_amount)+parseFloat(order_grandtotal_approved_online);
		    	order_grandtotal_approved=order_grandtotal_approved.toFixed(2);

			}else if(cancel_refund_type==3){
				voucher_amount=voucher_amount;
				order_grandtotal_approved_online='<?=number_format($order_grandtotal_approved_online,2)?>';
			}
			
			if($("input:checkbox[name='cheque_value']:checked").val()==1){
		   		checkboxRefund=1;
		    }else{
		    	checkboxRefund=0;
		    }
			var dataSubmit={
				'escalationsId':escalationsId,
				'order_grandtotal_approved':order_grandtotal_approved,
				'order_grandtotal_approved_online':order_grandtotal_approved_online,
				'cancel_refund_type':cancel_refund_type,
				'voucher_amount':voucher_amount,  
				'cheque_already_recived':checkboxRefund
			}

			$.ajax({
					url: BASE_URL+'ReturnOrderController/escalationsRefundMethod',
					data: dataSubmit,
					/*processData: false,
					contentType: false,*/
					type: 'POST',		
					/*async:false,
					dataType: 'json',*/
					beforeSend: function(){
						 $('#ajax-spinner').show();
					},
					success: function(response) {
						
						$('#ajax-spinner').hide();
						var obj = JSON.parse(response);
						if(obj.status == 200) {
							swal('Success',obj.message,'success');
							// setTimeout(function() {
	                            window.location.reload();
	                        // }, 1000);
							//window.location.href=BASE_URL+'webshop/shipped-order/detail/'+obj.order_id;
							
						}else{
							$('#confirm-returns-btn-mode').removeAttr("disabled");
							swal('Error',obj.message,'error');
							return false;
						}

					}      
				});

		}else{
			$('#errorMsg').html('Please select refund type');
		}
	}

	$('#cheque_value').change(function() {
		//alert('text');
		var cancelRefundType = $('#cancel_refund_type:checked').val();
		if(cancelRefundType==0){
			var grandValueCurrency="<?=$currency_code.' '.number_format(0,2)?>";
	   		$('.totalRefundAmount').html(grandValueCurrency);
        	$('#order_grandtotal_approved').val(0);
		}else{
	        if ($(this).prop('checked')) {
	        	//alert('text');
	        	var grandValueCurrency="<?=$currency_code.' '.number_format($product_grand_total_ref,2)?>";
	        	var grandValue='<?=number_format($product_grand_total_ref,2)?>';
	        	// console.log(grandValueCurrency);
	        	$('.totalRefundAmount').html(grandValueCurrency);
	        	$('#order_grandtotal_approved').val(grandValue);
	        }else {
	        	var grandValue=<?=$voucherAmount?>;
	        	if(grandValue >0.00 || grandValue > 0){
					var grandValueCurrency="<?=$currency_code.' '.number_format($voucherAmount,2)?>";
	        	}else{
	        		var grandValueCurrency="<?=$currency_code.' '.number_format($product_grand_total_ref,2)?>";
	        	}
	        	//console.log(grandValueCurrency);
	        	$('.totalRefundAmount').html(grandValueCurrency);
	        	$('#order_grandtotal_approved').val(grandValue);
	        }
   		}
    });

    $("input[name='cancel_refund_type']").click(function(){
	   var cancelPaymentMethod='<?=$payment_method_cancel?>';
	   var refundValue = this.value;
	   var checkboxRefund=0;
	   $('#order_grandtotal_approved').prop('readonly', false);
	   if($("input:checkbox[name='cheque_value']:checked").val()==1){
	   		checkboxRefund=1;
	   }else if(cancelPaymentMethod!=='cod' || cancelPaymentMethod=='via_transfer'){
	   		checkboxRefund=1;
	   }


	   //alert($("input:checkbox[name='cheque_value']:checked").val());
	   if(refundValue==0){
	   		var grandValueCurrency="<?=$currency_code.' '.number_format(0,2)?>";
	   		$('.totalRefundAmount').html(grandValueCurrency);
        	$('#order_grandtotal_approved').val(0);
        	$('#order_grandtotal_approved').prop('readonly', true);
	   }else if(refundValue==4){
	   	// alert();
	   		var grandValueCurrency="<?=$currency_code.' '.number_format($product_grand_total_ref,2)?>";
	   		var grandValue="<?=number_format($refundAmount,2)?>";
	   		$('.totalRefundAmount').html(grandValueCurrency);
        	$('#order_grandtotal_approved').val(grandValue);
	   }else if(refundValue==3){
	   		var grandValueCurrency="<?=$currency_code.' '.$product_grand_total_ref ?>";
	   		var grandValue="<?=number_format($product_grand_total_ref,2)?>";
	   		$('.totalRefundAmount').html(grandValueCurrency);
        	$('#order_grandtotal_approved').val(grandValue);
        	$('#order_grandtotal_approved').prop('readonly', true);
	   }else{

	   		if(checkboxRefund==1){
	   			
	   			var grandValueCurrency="<?=$currency_code.' '.number_format($product_grand_total_ref,2)?>";
	        	//console.log(grandValueCurrency);
	        	$('.totalRefundAmount').html(grandValueCurrency);
	        	$('#order_grandtotal_approved').val(<?=$product_grand_total_ref?>);
	   		}else{

		   		var grandValue=<?=$voucherAmount?>;
	        	if(grandValue >0.00 || grandValue > 0){
					var grandValueCurrency="<?=$currency_code.' '.number_format($voucherAmount,2)?>";
	        	}else{
	        		var grandValueCurrency="<?=$currency_code.' '.number_format($product_grand_total_ref,2)?>";
	        		grandValue= "<?=number_format($product_grand_total_ref,2)?>";
	        	}
	        	//console.log(grandValueCurrency);
	        	$('.totalRefundAmount').html(grandValueCurrency);
	        	$('#order_grandtotal_approved').val(grandValue);
	   		}

	   }
	});

</script>
