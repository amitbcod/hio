<div class="modal-header">
	<h4 class="head-name">Split order Confirmation</h4>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	  <span aria-hidden="true">×</span>
	</button>
	</div>
	<input type="hidden" name="hidden_order_id" name="hidden_order_id" value="<?php echo $order_id; ?>"/>
	<div class="modal-body">
		<p class="are-sure-message">Are you sure, you want to split the order ?</p>
		<div class="message-box-popup">
			<label>Message</label>
			<textarea class="form-control" id="split_message" name="split_message" placeholder="Message"></textarea>
		</div>
	</div>
<div class="modal-footer">
 <button class="purple-btn" type="button" onclick="SplitOrderConfirmed(<?php echo $order_id; ?>);">Confirm Order </button>
</div>
