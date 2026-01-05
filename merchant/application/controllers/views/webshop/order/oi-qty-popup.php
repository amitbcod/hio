<div class="modal-header">
	<h4 class="head-name">Product Quantity</h4>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	  <span aria-hidden="true">×</span>
	</button>
	
	</div>
	<div class="modal-body">
		<p class="are-sure-message">Ordered Quantity</p>
		<div class="message-box-popup col-sm-6">
			<input type="text" class="form-control" id="qty_ordered" name="qty_ordered" value="<?php echo $OrderItemData->qty_ordered; ?>">
		</div>
	</div>
<div class="modal-footer">
 <button class="purple-btn" type="button" id="conf-qty-btn" onclick="ConfirmQty(<?php echo $order_id; ?>,<?php echo $item_id; ?>);">Confirm</button>
</div>