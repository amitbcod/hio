<div class="modal-header">
	<h4 class="head-name">Product Price edit</h4>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	  <span aria-hidden="true">×</span>
	</button>
	
	</div>
	<div class="modal-body">
		<p class="are-sure-message">Product Price</p>
		<div class="message-box-popup col-sm-6">
			<input type="text" class="form-control" id="price_ordered" name="price_ordered" value="<?php echo $OrderItemData->price; ?>">
		</div>
	</div>
<div class="modal-footer">
 <button class="purple-btn" type="button" id="conf-price-btn" onclick="ConfirmPrice(<?php echo $order_id; ?>,<?php echo $item_id; ?>);">Confirm</button>
</div>