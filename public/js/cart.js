function updateCartItems(){
	$('#update-cart-form').on('submit',function(e){
		alert("df")
		e.preventDefault();
		//if($(this).valid()) {

			var fd = new FormData($('#update-cart-form')[0]);
            $.ajax({
                url: BASE_URL+'CartController/updateWholeCartItems',
                type: 'ajax',
                method: $(this).attr('method'),
                dataType: 'json',
                data: fd,
				processData: false,
				contentType: false,
                success: function(response) {
                    console.log(response);
					$('#ajax-spinner').hide();
                    if (response.flag == 1) {
                        swal({
							title: "",
							icon: "success",
							text: response.msg,
							buttons: false,
							//timer: 3000
						})

                        setTimeout(function() {
                            location.reload();
                        }, 1000);

                    } else {
                        swal({
							title: "",
							icon: "error",
							text: response.msg,
							buttons: false,
							timer: 3000
						})
                    }
                }
            });
		//}
	});
}

function increaseQtyValue(product_id,ptype='',id='',parent_product_id='') {
	var value = parseInt(jQuery('#quantity_'+product_id).val());
	
	value = isNaN(value) ? 0 : value;
	value++;

	if(value <= jQuery('#quantity_'+product_id).attr('max')  && ptype == 'bundle'){
		validateBundleQty(product_id,value);
	}

	if(value <= jQuery('#quantity_'+product_id).attr('max')  && ptype != 'bundle'){
		validateOtherProductsQty(product_id,value,id,parent_product_id);
	}
}

function decreaseQtyValue(product_id,ptype='',id='',parent_product_id='') {
	var value = parseInt(jQuery('#quantity_'+product_id).val());
	value = isNaN(value) ? 0 : value;
	value < 1 ? value = 1 : '';
	value--;

	if(value >= jQuery('#quantity_'+product_id).attr('min') && ptype == 'bundle'){
		validateBundleQty(product_id,value);
	}

	if(value >= jQuery('#quantity_'+product_id).attr('min') && ptype != 'bundle'){
		validateOtherProductsQty(product_id,value,id,parent_product_id);
	}
}

function updateItemQty(product_id,ptype,id='',parent_product_id=''){

	if(ptype == 'bundle'){
			var value =jQuery('#quantity_'+product_id).val()
			validateBundleQty(product_id,value);
	}else{
		var value =jQuery('#quantity_'+product_id).val();
		validateOtherProductsQty(product_id,value,id,parent_product_id);
	}
}

$(".cart-qty-item").change(function () {
	var product_type = jQuery(this).data('product-type');
	var id = jQuery(this).data('conf-simple');
	var parent_product_id = jQuery(this).data('parent-product-id');
	var product_id = jQuery(this).data('item-id');
	if(product_type == 'bundle'){
		validateBundleQty(product_id,$(this).val());
	}else{
		validateOtherProductsQty(product_id,$(this).val(),id,parent_product_id);
	}

});

function updateMiniCart(){
	$.ajax({
		type: 'POST',
		url: BASE_URL+'CartController/updateminicart',
		dataType: 'html',
		data: {},
		success: function(response){
			$("#mini-cart-main-container").html(response);
		}
	});
}

function RefreshCartOrderSidebar(){
	$.ajax({
		url: BASE_URL+'CheckoutController/refreshSidebar',
		type: 'POST',
		async:false,
		dataType:'html',
		data: {},
		success: function(response){
			if (response !='error') {
				$('#cart-page-sidebar').html(response);
			} else {
				return false;
			}
		}
	});

}


function validateBundleQty(item_id,quantity){
	$('#ajax-spinner').show();
	$("#qtyError_"+item_id).html('');
    $("#qtyError_"+item_id).show();
	var previous_qty = jQuery('#previous_qty_'+item_id).val();
	var conf_simple_array = [];
	var string =$('#bundle_child_ids_'+item_id).val();

	if(string != '' ) {
		var bundle_child_ids = string.split(',');
		$.each( bundle_child_ids, function( key, value) {
			var conf_simple_pid = $('#conf_simple_pid_'+item_id+'_'+value).val();
			conf_simple_array.push({"bundle_child_id":value,"conf_simple_pid":conf_simple_pid});
		});
	}

	$.ajax({
		url: BASE_URL+"ProductsController/getBundleChildValidateQty",
		type: "POST",
		dataType:"json",
		data: {
			product_id:'',
			qty:quantity,
			conf_simple_array:conf_simple_array
		},
		success: function(response) {
			if(response.status == 200){

				jQuery('#quantity_'+item_id).val(quantity);
				var qty = jQuery('#quantity_'+item_id).val();
				var max_qty = jQuery('#max_qty_'+item_id).val();
				var price = jQuery('#quantity_'+item_id).data('price');

				$.ajax({
					url: BASE_URL+'CartController/updateCartItems',
					type: 'ajax',
					method: 'POST',
					dataType: 'json',
					data:{item_id:item_id,quantity:qty,max_qty:max_qty,previous_qty:previous_qty,price:price},
					success: function(response) {
						if (response.flag == 1) {
							updateMiniCart();
							RefreshCartOrderSidebar();
							$("#qtyError_"+item_id).html('<span class="success-msg">'+response.msg+'</span>').fadeOut(5000);
							$("#item_total_price_"+item_id).html(response.price);
							$("#previous_qty_"+item_id).val(qty);
						} else {
							$("#qtyError_"+item_id).html('<span class="error-msg">'+response.msg+'</span>').fadeOut(5000);
							if(response.exceed_flag == 1){
								$('#quantity_'+item_id).val(response.previous_qty);
								$("#previous_qty_"+item_id).val(response.previous_qty);
								$("#item_total_price_"+item_id).html(response.price);
							}
						}
						$('#ajax-spinner').hide();
					}
				});

			}else{
				$('#ajax-spinner').hide();
				$("#qtyError_"+item_id).show();
				$('#quantity_'+item_id).val(previous_qty);
				$("#previous_qty_"+item_id).val(previous_qty);
				$("#qtyError_"+item_id).html('<span class="error-msg">'+'This Product qty '+quantity+' is not available.</span>').fadeOut(5000);
				return false;
			}
		}

	});
}

function validateOtherProductsQty(item_id,value,id,parent_product_id){

	$('#quantity_'+item_id).val(value);
	var qty = jQuery('#quantity_'+item_id).val();
	var max_qty = jQuery('#max_qty_'+item_id).val();
	var price = jQuery('#quantity_'+item_id).data('price');
	var previous_qty = jQuery('#previous_qty_'+item_id).val();

	if(qty <= 0){
		$('#quantity_'+item_id).val(previous_qty);
		$("#qtyError_"+item_id).show();
		$("#qtyError_"+item_id).html('<span class="error-msg">Unable to update item.</span>').fadeOut(5000);
		return false;
	}

	$("#qtyError_"+item_id).html('');
	$("#qtyError_"+item_id).show();
	$('#ajax-spinner').show();
	$.ajax({
		url: BASE_URL+"ProductsController/getSimpleValidateQty",
		type: "POST",
		dataType:"json",
		data: {
			product_id:id,
			parent_product_id:parent_product_id,
			qty:value,
		},
		success: function(response) {
			if(response.status == 200){

				$.ajax({
					url: BASE_URL+'CartController/updateCartItems',
					type: 'ajax',
					method: 'POST',
					dataType: 'json',
					data:{item_id:item_id,quantity:qty,max_qty:max_qty,previous_qty:previous_qty,price:price},
					success: function(response) {
						if (response.flag == 1) {
							updateMiniCart();
							RefreshCartOrderSidebar();
							$("#qtyError_"+item_id).html('<span class="success-msg">'+response.msg+'</span>').fadeOut(5000);
							$("#item_total_price_"+item_id).html(response.price);
							$("#previous_qty_"+item_id).val(qty);
						} else {
							$("#qtyError_"+item_id).html('<span class="error-msg">'+response.msg+'</span>').fadeOut(5000);
							if(response.exceed_flag == 1){
								$('#quantity_'+item_id).val(response.previous_qty);
								$("#previous_qty_"+item_id).val(response.previous_qty);
								$("#item_total_price_"+item_id).html(response.price);
							}
						}
						$('#ajax-spinner').hide();
					}
				});

			}else{
				$('#ajax-spinner').hide();
				$('#quantity_'+item_id).val(previous_qty);
				$("#previous_qty_"+item_id).val(previous_qty);
				$("#qtyError_"+item_id).show();
				$("#qtyError_"+item_id).html('<span class="error-msg">'+'This Product qty '+qty+' is not available.</span>').fadeOut(5000);
				return false;
			}
		}
	});

}
