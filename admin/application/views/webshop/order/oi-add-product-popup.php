<div class="modal-header">
	<h4 class="head-name">Add Product</h4>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	  <span aria-hidden="true">×</span>
	</button>
	
	</div>
	<div class="modal-body">
		<div class="message-box-popup col-12">
            <div class="form-group">
                <label>Product Name / SKU</label>
                <input type="text" class="form-control" id="sku" name="sku" placeholder="Product Name - SKU">
            </div>
            <div class="form-group">
                <label>Quantity</label>
                <input value="1" type="text" name="qty" id="qty" class="form-control" placeholder="Quantity">
            </div>
		</div>
	</div>
<div class="modal-footer">
 <button class="purple-btn" type="button" id="conf-price-btn" onclick="ConfirmAddProduct(<?php echo $order_id; ?>);">Confirm</button>
</div>

<script type="text/javascript">
$('#sku').autocomplete({
    minLength: 3,
    source: function(request, response) {
        $.getJSON(BASE_URL+"InboundController/getProductSku_for_add_product", {
            term: request.term
        }, function(data) {    
            var array = data.error ? [] : $.map(data, function(m) {
                return {
                    label: m.name+" - "+m.sku,
                    value: m.sku,
                };

            });
            response(array);
         });
    },

    select: function (event, ui) {
        $('#sku').val(ui.item.value); // save selected id to hidden input
        return false;
    },

    focus: function( event, ui ) {
        $('#sku').val(ui.item.label);
        return false;
    },	

    change: function( event, ui ) {
        $( "#sku" ).val( ui.item? ui.item.value : "" );
    }

});
</script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>seller-inbound-process.js"></script> 