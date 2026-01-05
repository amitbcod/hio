
function OpenSplitOrderPopup(order_id){
	if(order_id!=''){


		$.ajax({
				url: BASE_URL+"B2BOrdersController/openSplitOrderPopup",
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
				url: BASE_URL+"B2BOrdersController/splitorderconfirm",
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
							window.location.href=BASE_URL+'b2b/split-order/detail/'+obj.split_order_id;
						}else{
							window.location.href=BASE_URL+'b2b/split-orders';
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
				url: BASE_URL+"B2BOrdersController/confirmOrder",
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

						window.location.href=BASE_URL+'b2b/order/create-shipment/'+order_id;

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
				url: BASE_URL+'B2BOrdersController/createShipment',
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

						window.location.href=BASE_URL+'b2b/shipped-order/detail/'+obj.order_id;

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
				url: BASE_URL+'B2BOrdersController/printshipmentlabel',
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
						window.open(BASE_URL+'b2b/order/print-label/'+obj.order_id,'_blank');


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
					url: BASE_URL+'B2BOrdersController/saveTrackingId',
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

$(document).ready(function() {



});

// new invoice

function GenerateInvoice(){
		$('#generateInvoice').prop("disabled", true);
		//var formData = new FormData($('#create-shipment')[0]);

		var webshop_b2b_order_id = $('#webshop_b2b_order_id').val();
		var b2b_order_id = $('#b2b_order_id').val();
		//console.log(webshop_b2b_order_id);
		var webshop_order_id = $('#webshop_order_id').val();
		var webshop_parent_id = $('#webshopParentId').val();
		var webshop_shop_id = $('#webshop_shop_id').val();
		var webshop_fbc_user_id = $('#webshop_fbc_user_id').val();
		//console.log(webshop_parent_id);
		//return false;
		if(webshop_order_id > 0){

			$.ajax({
					url: BASE_URL+"B2BOrdersController/invoiceGenerate_b2b",
					type: "POST",
					data: {
					  webshop_b2b_order_id:webshop_b2b_order_id,
					  b2b_order_id:b2b_order_id,
					  order_id:webshop_order_id,
					  parent_id:webshop_parent_id,
					  webshop_fbc_user_id:webshop_fbc_user_id,
					  webshop_shop_id:webshop_shop_id
					},
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
							$('#generateInvoice').removeAttr("disabled");
							swal('Error',obj.message,'error');
							return false;
						}
					}
			});
		}

}
//end generate invoice
function DownloadInvoice(get_s3_base_url=''){
	    var s3_base_url = '';
	    if(get_s3_base_url == "") {
			s3_base_url = S3_URL;
		} else {
			s3_base_url = get_s3_base_url;
		}
	    $('#downloadInvoice').prop("disabled", true);
		var filename=$('#webshop_invoice_file').val();
		var webshop_shop_id = $('#webshop_shop_id').val();
		var webshop_fbc_user_id = $('#webshop_fbc_user_id').val();

		var pdfurl =s3_base_url+'invoices/'+filename;
		if(filename){
	    $.ajax({
	        url: pdfurl,
	        method: 'GET',
	        xhrFields: {
	            responseType: 'blob'
	        },
	        success: function (data) {
	            var a = document.createElement('a');
	            var url = window.URL.createObjectURL(data);
	            a.href = url;
	            a.download = filename;
	            document.body.append(a);
	            a.click();
	            a.remove();
	            window.URL.revokeObjectURL(url);
	            $('#downloadInvoice').removeAttr("disabled");
	        }
	    });
	}
}
//end generate invoice

/*shipping service*/

	$(".shipment_api").change(function() {
		var shipmentId=$(this).val();
		var webshop_order_id = $('#webshop_order_id').val();
		var webshop_parent_id = $('#webshopParentId').val();
		var webshop_shop_id = $('#webshop_shop_id').val();
		var webshop_shipping_pincode = $('#webshop_shipping_pincode').val();
		var webshop_b2b_order_id = $('#webshop_b2b_order_id').val();
		var b2b_order_id = $('#b2b_order_id').val();
		if(shipmentId==3){
			//alert(webshop_shipping_pincode);
			//
			$.ajax({
					url: BASE_URL+"B2BOrdersController/delhiveryApiPincodePrepaid",
					type: "POST",
					data: {
					  // webshop_b2b_order_id:webshop_b2b_order_id,
					  shipmentId:shipmentId,
					  b2b_order_id:b2b_order_id,
					  webshop_order_id:webshop_order_id,
					  webshop_parent_id:webshop_parent_id,
					  webshop_shipping_pincode:webshop_shipping_pincode,
					  webshop_shop_id:webshop_shop_id
					},
					beforeSend: function(){
						 $('#ajax-spinner').show();
					},
					success: function(response) {
						$('#ajax-spinner').hide();
						var obj = JSON.parse(response);
						if(obj.status == 200) {
							swal('Success',obj.message,'success');
							//location.reload();


						}else{
							// $('#shipment_id').attr('selected', false);
							$('#shipment_id').prop('selectedIndex',0);
							//$('#generateInvoice').removeAttr("disabled");
							swal('Error',obj.message,'error');
							return false;
						}
					}
			});
			//
		}
	});
/*shipping service*/

function PrintShippingLabel_table(order_id,order_shipment_id){
		if (order_id =='') {
			swal('Error','There is some issue, please try again','error');
			return false;
		}
		else {
			console.log(order_id);
			$.ajax({
				url: BASE_URL+'B2BOrdersController/printshipmentlabel_table',
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
						window.open(BASE_URL+'b2b/order/print-label-table/'+obj.order_id+'/'+obj.order_shipment_id,'_blank');

					}else{
						swal('Error',obj.message,'error');
						return false;
					}


				}
			});
	}
}

$('#global-b2b-order-search').keypress(function(event){
	var keycode = (event.keyCode ? event.keyCode : event.which);
	if(keycode == '13'){
		var increment_id = $(this).val()
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: BASE_URL+'B2BOrdersController/GetOrderByIncrementId',
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
