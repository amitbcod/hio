<div class="modal-header">
	<h4 class="head-name">Refund order Confirmation</h4>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	  <span aria-hidden="true">×</span>
	</button>
	
	</div>
	<input type="hidden" name="hidden_order_id" name="hidden_order_id" value="<?php echo $escalations_order_id; ?>"/>
	<div class="modal-body">
		<p class="are-sure-message">Are you sure, you want to confirm the refund ?</p>
		<div class="message-box-popup">
			<label>Message</label>
			<textarea class="form-control" id="refund_message" name="refund_message" placeholder="Message"></textarea>
		</div>
	</div>
<div class="modal-footer">
 <button class="purple-btn" type="button" id="refund-conf-btn" onclick="EscalationsOrderConfirmed(<?php echo $escalations_order_id; ?>);">Confirm refund </button>
</div>