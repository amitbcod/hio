var startdate;
var enddate;
// set default dates
var start = new Date();
$(document).ready(function() {
	// console.log(newSubStartDate);
	// console.log(newSubEndDate);

	// $('#start_date').datepicker({
	// 	endDate: start,
	// 	format: 'dd/mm/yyyy',
	// 	// format: 'dd-mm-yyyy',
	// 	autoclose: true,
	// 	setDate: new Date(newSubStartDate),
	// 	// update "toDate" defaults whenever "startdateDate" changes
	// }).on('changeDate', function() {
	// 	// set the "toDate" start to not be later than "startdateDate" ends:
	// 	var converted_date = $(this).val();
	// 	converted_date = converted_date.split('-');
	// 	converted_date = converted_date[2] + '-' + converted_date[1] + '-' + converted_date[0];
	// 	$('#end_date').datepicker('setStartDate', new Date(converted_date));
	// }).attr('readonly', 'readonly');

	// $('#end_date').datepicker({
	// 	startDate: new Date(),
	// 	// endDate: new Date(),
	// 	format: 'dd/mm/yyyy',
	// 	// setEndDate
	// 	// format: 'dd-mm-yyyy',
	// 	autoclose: true,
	// 	setDate: new Date(newSubEndDate),
	// 	// update "startdateDate" defaults whenever "toDate" changes
	// }).on('changeDate', function() {
	// 	// set the "startdateDate" end to not be later than "toDate" starts:
	// 	var converted_date = $(this).val();
	// 	converted_date = converted_date.split('-');
	// 	converted_date = converted_date[2] + '-' + converted_date[1] + '-' + converted_date[0];
	// 	$('#start_date').datepicker('setEndDate', new Date(converted_date));
	// }).attr('readonly', 'readonly');

	jQuery(function () {
		jQuery("#start_date").datepicker({
			dateFormat: "dd/mm/yy",
			changeMonth: true,
			changeYear: true,
			maxYear: "60",
			onClose: function (selectedDate) {
				jQuery("#end_date").datepicker("option", "minDate", selectedDate);

				// Add logic to check if start_date > end_date
				var startDate = jQuery("#start_date").datepicker("getDate");
				var endDate = jQuery("#end_date").datepicker("getDate");
				if (startDate > endDate) {
					jQuery("#end_date").datepicker("setDate", startDate);
				}
			},
		});

		jQuery("#end_date").datepicker({
			dateFormat: "dd/mm/yy",
			changeMonth: true,
			changeYear: true,
			minDate: "0",
			onClose: function (selectedDate) {
				jQuery("#start_date").datepicker("option", "maxDate", selectedDate);

				// Add logic to check if start_date > end_date
				var startDate = jQuery("#start_date").datepicker("getDate");
				var endDate = jQuery("#end_date").datepicker("getDate");
				if (startDate > endDate) {
					jQuery("#start_date").datepicker("setDate", endDate);
				}
			},
		});
	});


	// $("#start_date").datepicker({
    //     numberOfMonths: 2,
    //     onSelect: function (selected) {
    //         var dt = new Date(selected);
    //         dt.setDate(dt.getDate() + 1);
    //         $("#txtTo").datepicker("option", "minDate", dt);
    //     }
    // });
    // $("#end_date").datepicker({
    //     numberOfMonths: 2,
    //     onSelect: function (selected) {
    //         var dt = new Date(selected);
    //         dt.setDate(dt.getDate() - 1);
    //         $("#txtFrom").datepicker("option", "maxDate", dt);
    //     }
    // });
})
$('.copy_link').click(function (e) {
   e.preventDefault();
   var copyText = $(this).attr('href');
   document.addEventListener('copy', function(e) {
      e.clipboardData.setData('text/plain', copyText);
      e.preventDefault();
   }, true);
   document.execCommand('copy');
   console.log('copied text : ', copyText);
    swal({ title: "",text: "Link copied successfully", button: false, icon: 'success' })
    setTimeout(function() { window.location.reload(); }, 1000);
 });

$('#copy_request_link').click(function (e) {
	e.preventDefault();
	var copyText = $(this).attr('href');
	document.addEventListener('copy', function(e) {
		e.clipboardData.setData('text/plain', copyText);
		e.preventDefault();
	}, true);
	document.execCommand('copy');
		swal({ title: "",text: "Request payment link copied successfully", button: false, icon: 'success' })
		//setTimeout(function() { window.location.reload(); }, 1000);
});

$('#global-order-search').keypress(function(event){
	var keycode = (event.keyCode ? event.keyCode : event.which);
	if(keycode == '13'){
		var increment_id = $(this).val()
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: BASE_URL+'WebshopOrdersController/GetOrderByIncrementId',
			data: {increment_id:increment_id},
			success:function(response){
				if (response.flag == 1) {
					setTimeout(function() {
						window.location.href = response.redirect;
					}, 1000);
				}else{
					swal('Error',response.message,'error');
					return false;
				}
			}
		});
	}
});

function OpenSplitOrderPopup(order_id){
	if(order_id!=''){


		$.ajax({
				url: BASE_URL+"WebshopOrdersController/openSplitOrderPopup",
				type: "POST",
				data: {
				  order_id:order_id
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

function SplitOrderConfirmed(order_id){
	if(order_id!=''){

		var split_message=$('#split_message').val();
		var current_tab=$('#current_tab').val();
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/splitorderconfirm",
				type: "POST",
				data: {
				  order_id:order_id,
				  split_message:split_message,
				  current_tab:current_tab
				},
				beforeSend: function(){
					 $('#ajax-spinner').show();
				},
				success: function(response) {

					$('#ajax-spinner').hide();
					//console.log(response);return false;

					var obj = JSON.parse(response);

					if(obj.status == 200) {

						$("#FBCUserCommonModal").modal('hide');
						swal('Success',obj.message,'success');
						if(obj.split_order_id!='' && obj.split_order_id>0){
							window.location.href=BASE_URL+'webshop/split-order/detail/'+obj.split_order_id;
						}else{
							window.location.href=BASE_URL+'webshop/split-orders';
						}

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

function ConfirmOrder(order_id){
	if(order_id!=''){

		var current_tab=$('#current_tab').val();

		$.ajax({
				url: BASE_URL+"WebshopOrdersController/confirmOrder",
				type: "POST",
				data: {
				  order_id:order_id,
				  current_tab:current_tab
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

						window.location.href=BASE_URL+'webshop/order/create-shipment/'+order_id;

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

function CreateShipmentSave(){
		$('#createShipment').prop("disabled", true);
		var formData = new FormData($('#create-shipment')[0]);

		var order_id = $('#order_id').val();
		var shipment_id = $('#shipment_id').val();

		var box_ids = [];
		var box_val_count=0;
		var box_count=$('.item-weight-input').length;
		var box_ids = $('input[name="box_weight[]"]').map(function(){
				if(this.value>0){
					box_val_count++;
				}
				return this.value;
          }).get();



		if (box_ids =='' || box_ids == '[]' || (box_val_count<box_count)) {
			$('#createShipment').removeAttr("disabled");
			swal('Error','Please enter box weight','error');
			return false;
		}else if(shipment_id==''){
			$('#createShipment').removeAttr("disabled");
			swal('Error','Please select shipment service','error');
			return false;
		}
		else {

			$.ajax({
				url: BASE_URL+'WebshopOrdersController/createShipment',
				data: formData,
				processData: false,
				contentType: false,
				type: 'POST',
				//dataType: 'json',
				beforeSend: function(){
					 $('#ajax-spinner').show();
				},
				success: function(response) {
					 $('#ajax-spinner').hide();
					var obj = JSON.parse(response);

					if(obj.status == 200) {

						swal('Success',obj.message,'success');

						window.location.href=BASE_URL+'webshop/shipped-order/detail/'+obj.order_id;

					}else{
						$('#createShipment').removeAttr("disabled");
						swal('Error',obj.message,'error');
						return false;
					}

				}
			});

	}


}

function PrintShippingLabel(){

		var formData = new FormData($('#create-shipment')[0]);

		var order_id = $('#order_id').val();
		var shipment_id = $('#shipment_id').val();

		var box_ids = [];
		var box_val_count=0;
		var box_count=$('.item-weight-input').length;
		var box_ids = $('input[name="box_weight[]"]').map(function(){
				if(this.value>0){
					box_val_count++;
				}
				return this.value;
          }).get();



		if (box_ids =='' || box_ids == '[]' || (box_val_count<box_count)) {
			swal('Error','Please enter box weight','error');
			return false;
		}
		else {

			$.ajax({
				url: BASE_URL+'WebshopOrdersController/printshipmentlabel',
				data: formData,
				processData: false,
				contentType: false,
				type: 'POST',
				//dataType: 'json',
				beforeSend: function(){
					 $('#ajax-spinner').show();
				},
				success: function(response) {
					 $('#ajax-spinner').hide();
					var obj = JSON.parse(response);
					if(obj.status == 200) {
						window.open(BASE_URL+'webshop/order/print-label/'+obj.order_id,'_blank');


					}else{
						swal('Error',obj.message,'error');
						return false;
					}


				}
			});

	}


}


function SaveTrackingID(){

		var formData = new FormData($('#tracking-shipment-frm')[0]);

		var order_id = $('#order_id').val();

		if (order_id !='') {

				$.ajax({
					url: BASE_URL+'WebshopOrdersController/saveTrackingId',
					data: formData,
					processData: false,
					contentType: false,
					type: 'POST',
					//dataType: 'json',
					beforeSend: function(){
						 $('#ajax-spinner').show();
					},
					success: function(response) {
						$('#ajax-spinner').hide();
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


}

function RefreshOrderItems(order_id,current_tab='')
{
	if(order_id!=''){
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/refreshOrderItems",
				type: "POST",
				data: {
				  order_id:order_id,
				  current_tab:current_tab
				},
				success: function(response) {
					if(response!='error'){
						$('#order-item-outer').html(response);
					} else {
						$('#barcode-error').html('Something went wrong.');
						return false;
					}

				}
			});
	}else{
		return false;
	}

}

function OpenApproveProductPopup(order_id){
	if(order_id!=''){
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/openApproveProductPopup",
				type: "POST",
				data: {
				  order_id:order_id
				},
				success: function(response) {
					if(response!='error'){
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					}else{
						return false;
					}

				}
			});
	}else{
		return false;
	}

}

function OpenNotesPopup(order_id){
	if(order_id!=''){
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/openInternalNotesPopup",
				type: "POST",
				data: {
				  order_id:order_id
				},
				success: function(response) {
					if(response!='error'){
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					}else{
						return false;
					}

				}
			});
	}else{
		return false;
	}

}


function ConfirmNotes(order_id){
	if(order_id!=''){
		$('#conf-notes-btn').prop('disabled',true);
		var internal_notes=$('#internal_notes').val();
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/saveNotes",
				type: "POST",
				data: {order_id:order_id,internal_notes:internal_notes},
				success: function(response) {
					var obj = JSON.parse(response);
					if(obj.status == 200) {
						$("#FBCUserCommonModal").modal('hide');
						// play('beep-success');
						RefreshOrderItems(order_id);
						swal('Success',obj.message,'success');
						location.reload();
					} else {
						$('#conf-notes-btn').prop('disabled',false);
						// play('beep-error');
						swal('Error',obj.message,'error');
						return false;

					}

				}
			});

	}else{
		return false;
	}

}

function ConfirmApprove(order_id){
	if(order_id!=''){
		$('#conf-approve-btn').prop('disabled',true);

		$.ajax({
				url: BASE_URL+"WebshopOrdersController/order_approve",
				type: "POST",
				data: {
				  order_id:order_id
				},
				success: function(response) {
					var obj = JSON.parse(response);
					if(obj.status == 200) {
						$("#FBCUserCommonModal").modal('hide');
						// play('beep-success');
						RefreshOrderItems(order_id);
						swal('Success',obj.message,'success');
						location.reload();
					} else {
						$('#conf-approve-btn').prop('disabled',false);
						// play('beep-error');
						swal('Error',obj.message,'error');
						return false;

					}

				}
			});

	}else{
		return false;
	}

}

function PrintShippingLabel_table(order_id,order_shipment_id){
		if (order_id =='') {
			swal('Error','There is some issue, please try again','error');
			return false;
		}
		else {
			console.log(order_id);
			$.ajax({
				url: BASE_URL+'WebshopOrdersController/printshipmentlabel_table',
				type: 'POST',
				data: {
				  order_id:order_id,
				  order_shipment_id:order_shipment_id
				},
				//dataType: 'json',
				beforeSend: function(){
					 $('#ajax-spinner').show();
				},
				success: function(response) {
					 $('#ajax-spinner').hide();
					var obj = JSON.parse(response);
					if(obj.status == 200) {
						window.open(BASE_URL+'webshop/order/print-label-table/'+obj.order_id+'/'+obj.order_shipment_id,'_blank');

					}else{
						swal('Error',obj.message,'error');
						return false;
					}


				}
			});
	}
}

function OpenRequestPaymentPopup(order_id){
	if(order_id!=''){
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/openRequestPaymentPopup",
				type: "POST",
				data: {
				  order_id:order_id
				},
				success: function(response) {
					if(response!='error'){
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					}else{
						return false;
					}

				}
			});
	}else{
		return false;
	}

}

function ConfirmRequestPayment(order_id){
	var current_tab=$('#current_tab').val();
	if(order_id!=''){
		$('#conf-price-btn').prop('disabled',true);

		$.ajax({
				url: BASE_URL+"WebshopOrdersController/reqest_payment_confirm",
				type: "POST",
				data: {
				  order_id:order_id,
				  current_tab:current_tab
				},
				success: function(response) {
					var obj = JSON.parse(response);
					if(obj.status == 200) {
						$("#FBCUserCommonModal").modal('hide');
						// play('beep-success');
						RefreshOrderItems(order_id,current_tab);
						swal('Success',obj.message,'success');
						location.reload();
					} else {
						$('#conf-price-btn').prop('disabled',false);
						// play('beep-error');
						swal('Error',obj.message,'error');
						return false;

					}

				}
			});

	}else{
		return false;
	}

}

function OpenPaymentPopup(order_id){
	if(order_id!=''){
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/openPaymentNotesPopup",
				type: "POST",
				data: {
				  order_id:order_id
				},
				success: function(response) {
					if(response!='error'){
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					}else{
						return false;
					}

				}
			});
	}else{
		return false;
	}

}

function ConfirmPaymentNotes(order_id){
	if(order_id!=''){
		$('#conf-manualpayment-btn').prop('disabled',true);
		var payment_received_note=$('#manual_payment_notes').val();
		var payment_method_id=$('#payment_method_id').val();
		var payment_method_name=$('#payment_method_name').val();
		var payment_method=$('#payment_method').val();
		var payment_type=$('#payment_type').val();
		var data = {
	        order_id : order_id,
	        payment_method_id : payment_method_id,
	        payment_method_name : payment_method_name,
	        payment_method : payment_method,
	        payment_type : payment_type,
	        payment_received_note : payment_received_note,
	    };

		$.ajax({
				url: BASE_URL+"WebshopOrdersController/saveManualPaymentNotes",
				type: "POST",
				data: data,
				// data: {'order_id':order_id,'payment_method_id':payment_method_id,'payment_method_name':payment_method_name,'payment_method':payment_method,'payment_type':payment_type,'payment_received_note':manual_payment_notes},
				// data: {order_id:order_id,payment_method_id:payment_method_id,payment_method_name:payment_method_name,payment_method:payment_method,payment_type:payment_type,payment_received_note:manual_payment_notes},
				success: function(response) {
					var obj = JSON.parse(response);
					if(obj.status == 200) {
						$("#FBCUserCommonModal").modal('hide');
						// play('beep-success');
						RefreshOrderItems(order_id);
						swal('Success',obj.message,'success');
						location.reload();
					} else {
						$('#conf-notes-btn').prop('disabled',false);
						// play('beep-error');
						swal('Error',obj.message,'error');
						return false;

					}

				}
			});

	}else{
		return false;
	}

}

function OpenShippingPopup(order_id){
	if(order_id!=''){
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/openshippingPopup",
				type: "POST",
				data: {
				  order_id:order_id
				},
				success: function(response) {
					if(response!='error'){
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					}else{
						return false;
					}

				}
			});
	}else{
		return false;
	}

}

function ConfirmShipping(order_id){
	if(order_id!=''){
		$('#conf-notes-btn').prop('disabled',true);
		var shipping_method_input=$('#shipping_method_input').val();
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/saveshipping",
				type: "POST",
				data: {order_id:order_id,shipping_method_input:shipping_method_input},
				success: function(response) {
					var obj = JSON.parse(response);
					if(obj.status == 200) {
						$("#FBCUserCommonModal").modal('hide');
						// play('beep-success');
						RefreshOrderItems(order_id);
						swal('Success',obj.message,'success');
						location.reload();
					} else {
						$('#conf-notes-btn').prop('disabled',false);
						// play('beep-error');
						swal('Error',obj.message,'error');
						return false;

					}

				}
			});

	}else{
		return false;
	}

}

function OpenShippingAmountPopup(order_id,shipping_amount){
	if(order_id!=''){
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/openshippingAmountPopup",
				type: "POST",
				data: {
				  order_id:order_id,shipping_amount:shipping_amount,
				},
				success: function(response) {
					if(response!='error'){
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					}else{
						return false;
					}

				}
			});
	}else{
		return false;
	}

}

function OpenRequestPaymentPopup(order_id){
	if(order_id!=''){
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/openRequestPaymentPopup",
				type: "POST",
				data: {
				  order_id:order_id
				},
				success: function(response) {
					if(response!='error'){
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					}else{
						return false;
					}

				}
			});
	}else{
		return false;
	}

}

function ConfirmShippingAmount(order_id){
	if(order_id!=''){
		$('#conf-notes-btn').prop('disabled',true);
		var shipping_amount=$('#shipping_amount').val();
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/saveshippingamount",
				type: "POST",
				data: {order_id:order_id,shipping_amount:shipping_amount},
				success: function(response) {
					var obj = JSON.parse(response);
					if(obj.status == 200) {
						$("#FBCUserCommonModal").modal('hide');
						// play('beep-success');
						RefreshOrderItems(order_id);
						swal('Success',obj.message,'success');
						location.reload();
					} else {
						$('#conf-notes-btn').prop('disabled',false);
						// play('beep-error');
						swal('Error',obj.message,'error');
						return false;

					}

				}
			});

	}else{
		return false;
	}

}

function OpenGrandTotalPopup(order_id){

	if(order_id!=''){
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/OpenGrandTotalPopup",
				type: "POST",
				data: {
				  order_id:order_id
				},
				success: function(response) {
					if(response!='error'){
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					}else{
						return false;
					}

				}
			});
	}else{
		return false;
	}
}

function currency() {
    var id = $('#currency_option').val();
	var url = window.location.href;
	var order_id = url.substring(url.lastIndexOf('/') + 1);
    console.log(order_id);
    $.ajax({
            type:'POST',
            url:BASE_URL+"WebshopOrdersController/selectedcurrency",
            data:{'id':id,'order_id':order_id},
			dataType:'json',
            success:function(response){
				console.log(response.data.currency_rate);
                    if(response.flag == 1)
                    {
						$("#conversion_rate").val(response.data.currency_rate);
						$("#grand_total").val(response.data.total);
                    }
					else{
						return false;
					}

            },
			error: function(){
                return false;
       		 }

        });
}


$(document).focus().on("keyup", "#conversion_rate", function () {

	var selected_id = $('#currency_option').val();
	var conversion_rate = $('#conversion_rate').val();
	var url = window.location.href;
	var order_id = url.substring(url.lastIndexOf('/') + 1);

	$.ajax({
		type:'POST',
		url:BASE_URL+"WebshopOrdersController/CalculateCurrencyRate",
		data:{'selected_id':selected_id,'conversion_rate':conversion_rate,'order_id':order_id},
		dataType:'json',
		success:function(response){
			console.log(response);
			if(response.flag == 1)
			{
				$("#grand_total").val(response.data.currency_symbol+ " " + response.data.total);
			}
			else{
				return false;
			}

		}

	});

});

function ConfirmCurrencyRating(order_id){

	if(order_id!=''){
		$('#conf-notes-btn').prop('disabled',true);
		var currency_option_id=$('#currency_option').val();
		var conversion_rate=$('#conversion_rate').val();
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/UpdateGrandTotalValue",
				type: "POST",
				data: {order_id:order_id,currency_option_id:currency_option_id,conversion_rate:conversion_rate},
				dataType:'json',
				success:function(response){
					if(response.flag == 1) {
						$("#FBCUserCommonModal").modal('hide');
						RefreshOrderItems(order_id);
						swal('Success',response.message,'success');
						location.reload();
					}
					else
					{
						$('#conf-notes-btn').prop('disabled',false);
						swal('Error',response.message,'error');
						return false;

					}
				}

			});

	}else{
		return false;
	}

}


function DownloadOrderCSV(order_id){

	csv_id = $('#download_csv').val();

	if(csv_id == "") {
		return false;
	}

	var download_url;
	var file_name;

	if(csv_id == 2) {
		 download_url  = "ExportQueryToCsvController/export_order_commercial_invoice_compressed?order_id="+order_id;
		 file_name = "export_order_commercial_invoice_compressed";
	} else if(csv_id == 3) {

	 	download_url = "ExportQueryToCsvController/export_order_customer_details?order_id="+order_id;
		 file_name = "export_order_customer_details";
	 } else {
		 download_url =  "ExportQueryToCsvController/export_order_commercial_invoice_detailed?order_id="+order_id;
		 file_name = "export_order_commercial_invoice_detailed";
	 }
	$.ajax({
		        url: BASE_URL+download_url,
		        method: 'GET',
		        xhrFields: {
		            responseType: 'blob'
		        },
				success: function (data) {
					//console.log(data);
		            var a = document.createElement('a');
		            var url = window.URL.createObjectURL(data);
		            a.href = url;
		            a.download = file_name+order_id+'.csv';
		            document.body.append(a);
		            a.click();
		            a.remove();
		            window.URL.revokeObjectURL(url);
		        }
			});
}


function OpenVatAmountPopup(order_id){
	if(order_id!=''){
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/OpenVatAmountPopup",
				type: "POST",
				data: {
				  order_id:order_id
				},
				success: function(response) {
					if(response!='error'){
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					}else{
						return false;
					}

				}
			});
	}else{
		return false;
	}
}

function ConfirmTaxPercent(order_id){
	var tax_percent=$('#tax_percent').val();
	if(tax_percent!=''){
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/ConfirmTaxPercent",
				type: "POST",
				data: {
				  order_id:order_id,
				  tax_percent:tax_percent
				},
				success: function(response) {
					var obj = JSON.parse(response);
					if(obj.status == 200) {
						$("#FBCUserCommonModal").modal('hide')
						swal('Success',obj.message,'success');
						location.reload();
					} else {
						swal('Error',obj.message,'error');
						return false;
					}

				}
			});

	}else{
		return false;
	}

}
