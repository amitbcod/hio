<div class="modal-header">
	<h4 class="head-name">Manual Payment Method</h4>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	  <span aria-hidden="true">×</span>
	</button>
	<input type="hidden" id="payment_method_id" name="payment_method_id" value="<?php echo isset($PaymentMasterData->id)?$PaymentMasterData->id:''; ?>">
	<input type="hidden" id="payment_method_name" name="payment_method_name" value="<?php echo isset($PaymentMasterData->payment_gateway)?$PaymentMasterData->payment_gateway:''; ?>">
	<input type="hidden" id="payment_method" name="payment_method" value="<?php echo isset($PaymentMasterData->payment_gateway_key)?$PaymentMasterData->payment_gateway_key:''; ?>">
	<input type="hidden" id="payment_type" name="payment_type" value="0">
	
	</div>
	<div class="modal-body">
		<!-- <p class="are-sure-message">Product Delete</p> -->
		<p>Are you sure you want to add manual payment method for this order ?</p>
		<div class="message-box-popup">
			<label>Manual payment method notes</label>
			<textarea class="form-control" id="manual_payment_notes" name="manual_payment_notes"  placeholder="Manual payment method notes"></textarea>
		</div>
	</div>
<div class="modal-footer">
 <button class="purple-btn" type="button" id="conf-manualpayment-btn" onclick="ConfirmPaymentNotes(<?php echo $order_id; ?>);">Confirm</button>
 <button type="button" class="purple-btn" data-dismiss="modal">Close</button>
</div>