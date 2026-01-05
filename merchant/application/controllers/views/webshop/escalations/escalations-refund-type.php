<?php //print_r($order_grandtotal_approved);
	$order_grandtotal_approved = $OrderData->subtotal + $OrderData->payment_final_charge;
	$voucherAmount=$OrderData->voucher_amount;
	if(isset($OrderPaymentDetail->payment_method) && !empty($OrderPaymentDetail->payment_method)){
		$order_grandtotal_approved = 0;
		//echo $OrderPaymentDetail->payment_method;
		$payment_method_cancel=$OrderPaymentDetail->payment_method;
		$order_grandtotal_approved = $OrderData->subtotal + $OrderData->payment_final_charge;
		if($payment_method_cancel == 'via_transfer' || $payment_method_cancel == 'cod' ) {
				if($OrderData->voucher_code != '' && $OrderData->voucher_amount > 0.00 ) {
					$order_grandtotal_approved = $OrderData->voucher_amount;
					if($order_grandtotal_approved > 0 ){
						$none_cancel='';
						$store_cancel='checked';
						$offline_cancel='';
					}
				}else{
					$order_grandtotal_approved = 0;					
				}
		}
		if ($OrderData->voucher_code != '' && $OrderData->voucher_amount > 0.00 ) {
			$order_grandtotal_approved=$OrderData->voucher_amount;
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
        if ($strip_cancel_enable=="yes" && $payment_method_cancel=="stripe_payment") {
        	$order_escalations_data=$this->ReturnOrderModel->getSingleDataByID('sales_order_escalations',array('order_id'=>$OrderData->order_id),'');
	        if($OrderData->voucher_code != '' && $OrderData->voucher_amount > 0.00 && $order_escalations_data->order_type==0) {
				if ($order_escalations_data->order_type==0) {
				?>
		        	<div class="radio">      
			            <label><input type="radio" name="cancel_refund_type" id="cancel_refund_type" value="4" id="" <?php if(isset($strip_cancel)){echo $strip_cancel;} ?>>Online Refund ( Online Refund: <?php echo $OrderData->currency_symbol.($OrderData->grand_total); ?>, Store credit: <?php echo $OrderData->currency_symbol.($OrderData->voucher_amount); ?> )<span class="checkmark"></span></label>
			        </div>
			    <?php
				}
			}else{
				if ($order_escalations_data->order_type==0) {
				?>
		        	<div class="radio">      
			            <label><input type="radio" name="cancel_refund_type" id="cancel_refund_type" value="3" id="" <?php if(isset($strip_cancel)){echo $strip_cancel;} ?>>Online Refund<span class="checkmark"></span></label>
			        </div>
			    <?php
			    }				
			}
            
        }

        if ($payment_method_cancel=="paypal_express" ) { // paypal
        	$order_escalations_data=$this->ReturnOrderModel->getSingleDataByID('sales_order_escalations',array('order_id'=>$OrderData->order_id),'');
	        if($OrderData->voucher_code != '' && $OrderData->voucher_amount > 0.00 && $order_escalations_data->order_type==0) {
				?>
		        	<div class="radio">      
			            <label><input type="radio" name="cancel_refund_type" id="cancel_refund_type" value="4" id="" <?php if(isset($online_cancel)){echo $online_cancel;} ?>>Online Refund ( Online Refund: <?php echo $OrderData->currency_symbol.($OrderData->grand_total); ?>, Store credit: <?php echo $OrderData->currency_symbol.($OrderData->voucher_amount); ?> )<span class="checkmark"></span></label>
			        </div>
			    <?php
			}else{
				?>
		        	<div class="radio">      
			            <label><input type="radio" name="cancel_refund_type" id="cancel_refund_type" value="3" id="" <?php if(isset($online_cancel)){echo $online_cancel;} ?>>Online Refund<span class="checkmark"></span></label>
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
	// alert('test');
		$('#confirm-returns-btn-mode').attr("disabled");
		var cancel_refund_type = $("input[name='cancel_refund_type']:checked").val();
		var voucher_amount="<?=$OrderData->voucher_amount?>";
		var order_grandtotal_approved_online="<?=number_format($OrderData->grand_total,2)?>";
		if(cancel_refund_type){
			$('#errorMsg').html('');
			// swal('Success','obj.message','success');
			var escalationsId=$("#sales_order_escalations_id").val();
			var order_grandtotal_approved=$("#order_grandtotal_approved").val();
			if($("input:checkbox[name='cheque_value']:checked").val()==1){
		   		checkboxRefund=1;
		    }else{
		    	checkboxRefund=0;
		    }
		    if (cancel_refund_type==4) {

		    	order_grandtotal_approved=parseFloat(order_grandtotal_approved)+parseFloat(order_grandtotal_approved_online);
		    	order_grandtotal_approved==order_grandtotal_approved.toFixed(2);
		    	voucher_amount=$("#order_grandtotal_approved").val();
		    }
			var dataSubmit={
				'escalationsId':escalationsId,
				'order_grandtotal_approved':order_grandtotal_approved,
				'cancel_refund_type':cancel_refund_type,
				'cheque_already_recived':checkboxRefund,
				'voucher_amount':voucher_amount,
				'order_grandtotal_approved_online':order_grandtotal_approved_online,
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
	        	var grandValueCurrency="<?=$currency_code.' '.number_format($OrderData->subtotal + $OrderData->payment_final_charge,2)?>";
	        	var grandValue='<?=number_format($OrderData->subtotal + $OrderData->payment_final_charge,2)?>';
	        	// console.log(grandValueCurrency);
	        	$('.totalRefundAmount').html(grandValueCurrency);
	        	$('#order_grandtotal_approved').val(grandValue);
	        }else {
	        	var grandValue=<?=$voucherAmount?>;
	        	if(grandValue >0.00 || grandValue > 0){
					var grandValueCurrency="<?=$currency_code.' '.number_format($voucherAmount,2)?>";
	        	}else{
	        		var grandValueCurrency="<?=$currency_code.' '.number_format($OrderData->subtotal + $OrderData->payment_final_charge,2)?>";
	        	}
	        	//console.log(grandValueCurrency);
	        	$('.totalRefundAmount').html(grandValueCurrency);
	        	$('#order_grandtotal_approved').val(grandValue);
	        }
   		}
    });

    $("input[name='cancel_refund_type']").click(function(){
	   /*var refundValue = this.value;
	   var checkboxRefund=0;
	   $('#order_grandtotal_approved').prop('readonly', false);
	   if($("input:checkbox[name='cheque_value']:checked").val()==1){
	   		checkboxRefund=1;
	   }*/

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
	   		var grandValueCurrency="<?=$currency_code.' '.$OrderData->voucher_amount?>";
	   		var grandValue="<?=number_format($OrderData->voucher_amount,2)?>";
	   		$('.totalRefundAmount').html(grandValueCurrency);
        	$('#order_grandtotal_approved').val(grandValue);
	   }else if(refundValue==3){
	   		var grandValueCurrency="<?=$currency_code.' '.$OrderData->grand_total ?>";
	   		var grandValue="<?=number_format($OrderData->grand_total,2)?>";
	   		$('.totalRefundAmount').html(grandValueCurrency);
        	$('#order_grandtotal_approved').val(grandValue);
        	$('#order_grandtotal_approved').prop('readonly', true);
	   }else{
	   		if(checkboxRefund==1){
	   			var grandValueCurrency="<?=$currency_code.' '.number_format($OrderData->subtotal + $OrderData->payment_final_charge,2)?>";
	        	//console.log(grandValueCurrency);
	        	$('.totalRefundAmount').html(grandValueCurrency);
	        	$('#order_grandtotal_approved').val(<?=$OrderData->subtotal + $OrderData->payment_final_charge?>);

	   		}else{
	   			var grandValue=<?=$voucherAmount?>;
	        	if(grandValue >0.00 || grandValue > 0){
					var grandValueCurrency="<?=$currency_code.' '.number_format($voucherAmount,2)?>";
	        	}else{
	        		var grandValueCurrency="<?=$currency_code.' '.number_format($OrderData->subtotal + $OrderData->payment_final_charge,2)?>";
	        		grandValue= "<?=number_format($OrderData->subtotal + $OrderData->payment_final_charge,2)?>";
	        	}
	        	//console.log(grandValueCurrency);
	        	$('.totalRefundAmount').html(grandValueCurrency);
	        	$('#order_grandtotal_approved').val(grandValue);
	   		}

	   }
	});

</script>
