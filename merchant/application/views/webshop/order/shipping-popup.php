<div class="modal-header">
	<h4 class="head-name">Shipping Methods</h4>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	  <span aria-hidden="true">×</span>
	</button>
	
	</div>
	<div class="modal-body">
		<!-- <p class="are-sure-message">Product Delete</p> -->
		<!-- <div class="message-box-popup col-sm-6">
			<p>Are you sure you want to approve this order ?</p>
			
		</div> -->
		<div class="message-box-popup">
			<select class="custom-select" name="shipping_method_input" id="shipping_method_input">
			  <option value="" >Select Shipping Method</option>
			  <?php foreach ($shipping_methods as $key => $value) {
			  	?>
			  		<option value="<?php echo $value->id; ?>" <?php if ($OrderItemData->ship_method_id==$value->id) { echo "selected"; } ?>><?php echo $value->ship_method_name; ?></option>
			  	<?php
			  } ?>
			</select>
				
		</div>
	</div>
<div class="modal-footer">
 <button class="purple-btn" type="button" id="conf-notes-btn" onclick="ConfirmShipping(<?php echo $order_id; ?>);">Confirm</button>
 <button type="button" class="purple-btn" data-dismiss="modal">Close</button>
</div>