<?php 
	if(isset($cancel_order) && $cancel_order=='able_to_cancel'){ 
	$payment_method_cancel='';

	$order_grandtotal_approved = $OrderData->subtotal + $OrderData->payment_final_charge;

	if(isset($OrderPaymentDetail->payment_method) && !empty($OrderPaymentDetail->payment_method)){
		//echo $OrderPaymentDetail->payment_method;
		$payment_method_cancel=$OrderPaymentDetail->payment_method;
		if($payment_method_cancel=='cod'){
			$none_cancel='checked';
			$store_cancel='disabled';
			$offline_cancel='disabled';
		}elseif($payment_method_cancel=='via_transfer'){
			$none_cancel='checked';
			$store_cancel='';
			$offline_cancel='';
		}else{
			$none_cancel='';
			$store_cancel='checked';
			$offline_cancel='';
		}


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

		// changes after removed popup cancel_refund_type none, store credit, transfer
		$order_grandtotal_approved = 0;

	}

?>
<div id="cancel-order-modal" class="modal fade" role="dialog">
	<div class="modal-dialog  modal-lg">
	<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="head-name">Delete Order</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">×</span>
				</button>
				
			</div>
			<div class="modal-body">
				<p class="are-sure-message">Are you sure you want to delete this order ?</p>
				<div class="message-box-popup">
					<label>Reason for deletion</label>
					<textarea class="form-control" id="cancel_reason" name="cancel_reason"  placeholder="Reason for deletion"></textarea>
				</div>
				<!-- <div class="checkbox-group">
					<div class="radio">       
	                    <label><input type="radio" name="cancel_refund_type" id="cancel_refund_type" value="1" id="" <?php if(isset($store_cancel)){echo $store_cancel;} ?>>Store Credit <span class="checkmark"></span></label>
	                </div> 
	                <div class="radio">       
	                    <label><input type="radio" name="cancel_refund_type" id="cancel_refund_type" value="2" id="" <?php if(isset($offline_cancel)){echo $offline_cancel;} ?>>Offline Refund<span class="checkmark"></span></label>
	                </div> 
	                <div class="radio">       
	                    <label><input type="radio" name="cancel_refund_type"  id="cancel_refund_type" value="0" id="" <?php if(isset($none_cancel)){echo $none_cancel;} ?>>None <span class="checkmark"></span></label>
	                </div> 
				</div> -->
			</div>
			<?php //print_r($OrderData);?>
			<input type="hidden" id="customer_email" value="<?=$OrderData->customer_email?>">
			<input type="hidden" id="customer_name" value="<?=$OrderData->customer_firstname.' '.$OrderData->customer_lastname?>">
			<input type="hidden" id="cancel_order_id" value="<?=$OrderData->order_id?>">
			<input type="hidden" id="esc_id" value="<?=$OrderData->increment_id?>">
			<input type="hidden" id="cancel_parent_id" value="<?=$OrderData->parent_id?>">
			<input type="hidden" id="cancel_main_parent_id" value="<?=$OrderData->main_parent_id?>">
			<?php 
				// refund amount cancel


			?>
			<input type="hidden" id="order_grandtotal_approved" value="<?=$order_grandtotal_approved?>">
			<input type="hidden" id="cancel_shipment_type" value="<?php if(isset($OrderData->shipment_type)){echo $OrderData->shipment_type;}?>">
			<div class="modal-footer">
				 <button class="purple-btn cancelOrderBtn" type="submit" onclick="OrderCancel();">Yes  </button>
				 <button class="purple-btn" type="button" data-dismiss="modal" >No  </button>
			</div> 
		</div>
	</div>
</div>
<?php } ?>

<script type="text/javascript">
	function OrderCancel() {
		var cancelReason=$('#cancel_reason').val();
		if(cancelReason){
			$('.cancelOrderBtn').prop("disabled", true);
			var order_id = $('#cancel_order_id').val();
			var parent_id = $('#cancel_parent_id').val();
			var main_parent_id = $('#cancel_main_parent_id').val();
			var customer_email = $('#customer_email').val();
			var customer_name = $('#customer_name').val();
			var esc_id = $('#esc_id').val();
			var order_grandtotal_approved = $('#order_grandtotal_approved').val();
			var cancel_refund_type = $("input[name='cancel_refund_type']:checked").val();
			if(cancel_refund_type==0){
				order_grandtotal_approved=0;
			}
			var cancel_reason = $('#cancel_reason').val();
			var cancel_shipment_type = $('#cancel_shipment_type').val();
			var dataSubmit={
				'order_id':order_id,
				'parent_id':parent_id,
				'main_parent_id':main_parent_id,
				'order_grandtotal_approved':order_grandtotal_approved,
				'customer_name':customer_name,
				'customer_email':customer_email,
				'esc_id':esc_id,
				'cancel_refund_type':cancel_refund_type,
				'cancel_reason':cancel_reason,
				'cancel_shipment_type':cancel_shipment_type
			}
				$.ajax({
					url: BASE_URL+'WebshopOrdersController/cancelOrderSplit',
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
							setTimeout(function() {
	                            window.location.reload();
	                        }, 1000);
							//window.location.href=BASE_URL+'webshop/shipped-order/detail/'+obj.order_id;
							
						}else{
							$('.cancelOrderBtn').removeAttr("disabled");
							swal('Error',obj.message,'error');
							return false;
						}

					}      
				});

		}else{
			$( "#cancel_reason" ).focus();
			return false;
		}				
	}

</script>