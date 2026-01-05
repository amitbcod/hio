<div class="modal-header">
	<h4 class="head-name">Taxes (Vat) Amount :</h4>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	  <span aria-hidden="true">×</span>
	</button>
	
	</div>
	<div class="modal-body">
		<p class="are-sure-message">Tax Percent :</p>
		<div class="message-box-popup col-sm-6">
			<input type="text" class="form-control" id="tax_percent" name="tax_percent" value="<?php echo $OrderItemData->tax_percent; ?>">
		</div>
	</div>
<div class="modal-footer">
 <button class="purple-btn" type="button" id="conf-qty-btn" onclick="ConfirmTaxPercent(<?php echo $order_id; ?>)">Confirm</button>
</div>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_order_detail.js?v=<?php echo CSSJS_VERSION; ?>"></script>