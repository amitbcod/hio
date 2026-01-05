
function RejectReturnRequest(return_order_id){
	if(return_order_id!=''){

		swal({
			title: "Are you sure ??",
			text: "You want to reject return for this order?", 
			icon: "warning",
			buttons: true,
			className: 'swal-height',
			dangerMode: true,
			showCancelButton: true,
		},function(isConfirm) 
		{
			if(isConfirm){
		

				$.ajax({ 
						url: BASE_URL+"ReturnOrderController/rejectReturnRequest",
						type: "POST",
						data: {
						return_order_id:return_order_id
						},
						beforeSend: function(){
							$('#ajax-spinner').show();
						},	
						success: function(response) {
								
							$('#ajax-spinner').hide();
							//console.log(response);return false;
						
							var obj = JSON.parse(response);
							
							if(obj.status == 200) {
						
								swal('Success',obj.message,'success');
								
								location.reload();
								
							}else{
								swal('Error',obj.message,'error');
								return false;
							}
							
					}
				});
				
			}
		});		



		
	}else{
		return false;
	}
}



function ConfirmReturnRequest(return_order_id){
	if(return_order_id!=''){

		if($('#return_recieved_date').val() == ''){

			swal('Error','Please enter recieved date.','error');
			return false;

		}
		
	
		
		$.ajax({ 
				url: BASE_URL+"ReturnOrderController/confirmReturnRequest",
				type: "POST",
				data: {
				  return_order_id:return_order_id
				},
				beforeSend: function(){
					 $('#ajax-spinner').show();
				},	
				success: function(response) {
						
					$('#ajax-spinner').hide();
					//console.log(response);return false;
					
					var obj = JSON.parse(response);
					
					if(obj.status == 200) {
				
						swal('Success',obj.message,'success');
						
						window.location.href=BASE_URL+'webshop/orders/return-request';
						
					}else{
						swal('Error',obj.message,'error');
						return false;
					}
					
			}
		});
		
	}else{
		return false;
	}
}

function saveReturnReceiveDate(return_recieved_date,return_order_id){
	if(return_order_id!=''){
		//alert(return_recieved_date);
		
		$.ajax({ 
				url: BASE_URL+"ReturnOrderController/saveReturnReceiveDate",
				type: "POST",
				data: {
				  return_order_id:return_order_id,
				  return_recieved_date:return_recieved_date
				},
				beforeSend: function(){
					
				},	
				success: function(response) {
					
					//console.log(response);return false;
					
					var obj = JSON.parse(response);
					
					if(obj.status == 200) {
				
						return true;
						
					}else{
						swal('Error',obj.message,'error');
						return false;
					}
					
			}
		});
		
	}else{
		return false;
	}
}

$(document).ready(function() {	
	


});


function OpenConfirmRefundPopup(return_order_id){
	if(return_order_id!=''){
		
		
		$.ajax({ 
				url: BASE_URL+"ReturnOrderController/openConfirmRefundPopup",
				type: "POST",
				data: {
				  return_order_id:return_order_id
				},
				beforeSend: function(){
					 $('#ajax-spinner').show();
				},	
				success: function(response) {
				$('#ajax-spinner').hide();
				if(response!='error'){
				
					$("#FBCUserCommonModal").modal();
					$('#modal-content').addClass('split-order-confirmation-popup');
					
					$("#modal-content").html(response);
				}else{
					
				}
					
			}
		});
		
	}else{
		return false;
	}
	
}


function RefundOrderConfirmed(return_order_id){
	if(return_order_id!=''){
		
		$('#refund-conf-btn').attr('disabled',true);
		
		var refund_message=$('#refund_message').val();
	
		$.ajax({ 
				url: BASE_URL+"ReturnOrderController/refundorderconfirm",
				type: "POST",
				data: {
				  return_order_id:return_order_id,
				  refund_message:refund_message
				},
				beforeSend: function(){
					 $('#ajax-spinner').show();
				},	
				success: function(response) {
						$('#refund-conf-btn').attr('disabled',false);
			
					$('#ajax-spinner').hide();
					//console.log(response);return false;
					
					var obj = JSON.parse(response);
					
					if(obj.status == 200) {
					
						$("#FBCUserCommonModal").modal('hide');
						swal('Success',obj.message,'success');
						window.location.href=BASE_URL+'webshop/orders/refund-request';
						
					}else{
						swal('Error',obj.message,'error');
						return false;
					}
					
			}
		});
		
	}else{
		return false;
	}
}



function RejectRefundRequest(return_order_id){
	if(return_order_id!=''){
		
		$.ajax({ 
				url: BASE_URL+"ReturnOrderController/rejectRefundRequest",
				type: "POST",
				data: {
				  return_order_id:return_order_id
				},
				beforeSend: function(){
					 $('#ajax-spinner').show();
				},	
				success: function(response) {
						
					$('#ajax-spinner').hide();
					//console.log(response);return false;
				
					var obj = JSON.parse(response);
					
					if(obj.status == 200) {
				
						swal('Success',obj.message,'success');
						
						location.reload();
						
					}else{
						swal('Error',obj.message,'error');
						return false;
					}
					
			}
		});
		
	}else{
		return false;
	}
}

function OpenConfirmEscalationsPopup(escalations_order_id){
	if(escalations_order_id!=''){
		
		
		$.ajax({ 
				url: BASE_URL+"ReturnOrderController/openConfirmEscalationsPopup",
				// url: BASE_URL+"ReturnOrderController/openConfirmRefundPopup",
				type: "POST",
				data: {
				  escalations_order_id:escalations_order_id
				},
				beforeSend: function(){
					 $('#ajax-spinner').show();
				},	
				success: function(response) {
				$('#ajax-spinner').hide();
				if(response!='error'){
				
					$("#FBCUserCommonModal").modal();
					$('#modal-content').addClass('split-order-confirmation-popup');
					
					$("#modal-content").html(response);
				}else{
					
				}
					
			}
		});
		
	}else{
		return false;
	}
	
}

function EscalationsOrderConfirmed(escalations_order_id){
	if(escalations_order_id!=''){
		
		$('#refund-conf-btn').attr('disabled',true);
		
		var refund_message=$('#refund_message').val();
		
		$.ajax({ 
				url: BASE_URL+"ReturnOrderController/escalationsorderconfirm",
				type: "POST",
				data: {
				  escalations_order_id:escalations_order_id,
				  refund_message:refund_message
				},
				beforeSend: function(){
					 $('#ajax-spinner').show();
				},	
				success: function(response) {

						$('#refund-conf-btn').attr('disabled',false);
			
					$('#ajax-spinner').hide();
					//console.log(response);return false;
					
					var obj = JSON.parse(response);
					//
					if(obj.status == 200) {
						$("#FBCUserCommonModal").modal('hide');
						swal('Success',obj.message,'success');
						window.location.href=BASE_URL+'webshop/orders/escalations-completed';
						// window.location.href=BASE_URL+'webshop/orders/escalations-request';
						
					}else{
						swal('Error',obj.message,'error');
						return false;
					}
					
			}
		});
		
	}else{
		return false;
	}
}


$("#add_shipping_cost").change(function(){   // 1st
	//var shipping_charge_flag =0;
    var checked = $(this).is(':checked');
    var shippingAmount = $('#shipping_amount').val();
    var return_order_id = $('#returns_id').val();
    var return_approved_val = $('#order_grandtotal_approved').val();
    if(checked==true){
    	var shipping_charge_flag =1;
    	var return_approved = parseFloat(return_approved_val) + parseFloat(shippingAmount);
    }else{
    	var shipping_charge_flag =0;
    	var return_approved = parseFloat(return_approved_val) - parseFloat(shippingAmount);
    }
    // alert(return_approved);return false;
    if(checked) {
        swal({
        title: "Are you sure ??",
        text: "You want to add shipping cost?", 
        icon: "warning",
        buttons: true,
        className: 'swal-height',
        dangerMode: true,
        showCancelButton: true,
        },function(isConfirm){
            if (isConfirm) {
                $("#add_shipping_cost").prop("checked", true);
                $.ajax({ 
						url: BASE_URL+"ReturnOrderController/shippingCost",
						type: "POST",
						data: {
						  shipping_charge_flag:shipping_charge_flag,
						  return_order_id:return_order_id,
						  return_approved:return_approved,
						  shippingAmount:shippingAmount
						},
						beforeSend: function(){
							 $('#ajax-spinner').show();
						},	
						success: function(response) {
							$('#ajax-spinner').hide();
							var obj = JSON.parse(response);
							if(obj.status == 200) {
								$("#FBCUserCommonModal").modal('hide');
								location.reload(true);
							}else{
								swal('Error',obj.message,'error');
								return false;
							}
							
					}
				});
            } else {
                $('#add_shipping_cost').removeAttr('checked');
            }
        });
    }else{
        swal({
        title: "Are you sure ??",
        text: "You want to removed shipping cost?", 
        icon: "warning",
        buttons: true,
        className: 'swal-height',
        dangerMode: true,
        showCancelButton: true,
        },function(isConfirm){
            if (isConfirm) {    
                $('#add_shipping_cost').removeAttr('checked');
                $.ajax({ 
						url: BASE_URL+"ReturnOrderController/shippingCost",
						type: "POST",
						data: {
						  shipping_charge_flag:shipping_charge_flag,
						  return_order_id:return_order_id,
						  return_approved:return_approved,
						  shippingAmount:0
						},
						beforeSend: function(){
							 $('#ajax-spinner').show();
						},	
						success: function(response) {
							$('#ajax-spinner').hide();
							var obj = JSON.parse(response);
							
							if(obj.status == 200) {
							
								$("#FBCUserCommonModal").modal('hide');
								location.reload(true);
								
							}else{
								swal('Error',obj.message,'error');
								return false;
							}
							
					}
				});
            } else {                   
                $("#add_shipping_cost").prop("checked", true);
                
            }
        });


    } 

});