function ReturnRequest(order_id,increment_id){
		var selected_item=[];
		$(".ro-check-item-"+order_id+":checked").each(function(index){
			var  element = {};
			var qty_ordered=$(this).data('qty_ordered');
			var item_id=$(this).val();
			var qty_return=$('#item_qty_'+item_id).val();
			element.qty_ordered = qty_ordered;
			element.item_id = item_id;
			element.qty_return = qty_return;
			selected_item.push(element);
		});

		if(selected_item=='' || selected_item=='[]'){
			swal("Error", "Please select at least one item.", "error");
			return false;
		}else{
			$('#ro-check-item-'+order_id).prop('disabled',true);
			$.ajax({
				type: 'POST',
				url: BASE_URL+'MyGuestOrdersController/addReturnRequest',
				dataType: 'json',
				data: {order_id:order_id, increment_id:increment_id, selected_item:selected_item},
				beforeSend: function(){
					$('#ajax-spinner').show();
				},
				success: function(response){
					//console.log(response);return false;
					$('#ro-check-item-'+order_id).prop('disabled',false);
					if(response.flag==1){
						window.location.href=response.redirect_to;
					}else{
						swal("Error", response.msg, "error");
						return false;
					}
				}
			});
		}
}

$(document).on("click", ".modalLink", function () {
	var passedID = $(this).data('id');
	$("#order_id").val(passedID);
});

$(document).on("click", ".tracking_details_btn", function () {

	var order_id = $(this).attr('orderid');
	$('#tracking_details_div_'+order_id).toggleClass('d-none');

	$.ajax({
		type: 'POST',
		url: BASE_URL+'MyGuestOrdersController/show_tracking_details',
		dataType: 'html',
		data: {order_id:order_id},
		beforeSend: function(){
			// $('#ajax-spinner').show();
		},
		success: function(response){
			$('#tracking_details_div_'+order_id).html(response);
			
		}
	});

});