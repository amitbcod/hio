<div class="modal-header">
	<h4 class="head-name">Edit Shipping Amount</h4>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	  <span aria-hidden="true">×</span>
	</button>
	
	</div>
	<div class="modal-body">
		<!-- <p class="are-sure-message">Product Delete</p> -->
		<div class="message-box-popup col-sm-12">
			<p>Are you sure you want to edit shipping amount for this order ?</p>
			
		</div>
		<div class="message-box-popup col-sm-6">
			<label>Shipping Amount</label>
			<input type="text" class="form-control" id="shipping_amount" name="shipping_amount" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1').replace(/^0[^.]/, '0');" value="<?php echo $shipping_amount; ?>">
		</div>
	</div>
<div class="modal-footer">
 <button class="purple-btn" type="button" id="conf-notes-btn" onclick="ConfirmShippingAmount(<?php echo $order_id; ?>);">Confirm</button>
 <button type="button" class="purple-btn" data-dismiss="modal">Close</button>
</div>