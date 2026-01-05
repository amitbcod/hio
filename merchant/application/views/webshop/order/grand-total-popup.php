<div class="modal-header">
	<h4 class="head-name">Update Order Currency & Conversion Rate: (Grand Total : <?php echo $currency_code . " " .$currencies_detail->grand_total ?> )</h4>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	  <span aria-hidden="true">×</span>
	</button>
	
	</div>
	<div class="modal-body">
		
		<div class="message-box-popup mb-3">
			<label>Order Placed In</label>
			<select class="custom-select" name="currency_option" id="currency_option" onchange="currency()">
			  <option value="" >Select currencies</option>
			  <?php foreach ($multi_currencies as $key => $value) {
			  	?>
			  		<option value="<?php echo $value->id; ?>" <?php if ($currencies_detail->currency_code_session == $value->code) { echo "selected"; }?>> <?php echo  $value->code. " " .$value->symbol. " - " .$value->name;?></option>
			  	<?php
			  } ?>
			</select>			
		</div>

		<div class="message-box-popup mb-3">
			<label>Conversion Rate</label>
			<input type="text" class="form-control" id="conversion_rate" name="conversion_rate"  value="<?php echo $currencies_detail->currency_conversion_rate?>" >
		</div>

		<div class="message-box-popup">
			<label>Grand Total</label>
			<input type="text" class="form-control" id="grand_total" name="grand_total"  value="<?php echo $currencies_detail->currency_symbol. " " . number_format($currencies_detail->grand_total*$currencies_detail->currency_conversion_rate, 2, '.', ''); ?>" readonly>
		</div>


	</div>
<div class="modal-footer">
 <button class="purple-btn" type="button" id="conf-notes-btn" onclick="ConfirmCurrencyRating(<?php echo $order_id; ?>);">Save</button>
 <button type="button" class="purple-btn" data-dismiss="modal">Close</button>
</div>



