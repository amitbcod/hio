<div class="modal-header">
	<h4 class="head-name">Request Payment</h4>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	  <span aria-hidden="true">×</span>
	</button>
	
	</div>
	<div class="modal-body">
		<!-- <p class="are-sure-message">Product Delete</p> -->
		<div class="message-box-popup col-sm-6">
			<p>Are you sure you want to request a payment ?</p>
			
		</div>
	</div>
<div class="modal-footer">
 <button class="purple-btn" type="button" id="conf-price-btn" onclick="ConfirmRequestPayment(<?php echo $order_id; ?>);">Confirm</button>
 <button type="button" class="purple-btn" data-dismiss="modal">Close</button>
</div>