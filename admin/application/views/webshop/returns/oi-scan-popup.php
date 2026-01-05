<div class="modal-header">
	<h4 class="head-name">Manual Item Scan</h4>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	  <span aria-hidden="true">×</span>
	</button>
	
	</div>
	<input type="hidden" name="hidden_order_id" name="hidden_order_id" value="<?php echo $return_order_id; ?>"/>
	<div class="modal-body">
		<p class="are-sure-message">Please select quantity</p>
		<div class="message-box-popup col-sm-6">
			<?php $remain_qty_scan=$OrderItemData->qty_return-$OrderItemData->qty_return_recieved; ?>
			<select name="qty_scan"  id="qty_scan" class="form-control">
			<?php for($i=1;$i<=$remain_qty_scan;$i++){ ?>
			<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
			<?php } ?>
			</select>
		</div>
	</div>
<div class="modal-footer">
 <button class="purple-btn" type="button" id="conf-qty-scan-btn" onclick="ConfirmQtyScan(<?php echo $return_order_id; ?>,<?php echo $return_order_item_id; ?>);">Confirm Scan </button>
</div>