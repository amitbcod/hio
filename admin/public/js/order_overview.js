$("#orderForm").validate({
	ignore: ':hidden',		
	
	beforeSend: function(){
		$('#ajax-spinner').show();
	},
	submitHandler: function(form) {
		var formData = new FormData($('#orderForm')[0]);
		$.ajax({
			url: form.action,
			type: 'ajax',
			method: form.method,
			dataType: 'json',
			data: formData,
			processData: false,
			contentType: false,
			cache: false,
			success: function(response) {
				$('#ajax-spinner').hide();
				
				if (response.flag == 1) {
					swal({ title: "",text: response.msg, button: false, icon: 'success' })
					//$('#search-tab').html(response.msg);
					 setTimeout(function() {
						window.location.href = response.redirect;
					}, 1000);
				} else {
					swal({ title: "",text: response.msg, button: false, icon: 'error' })
					return false;
				}
			}
		});
	}
});

function viewAppliedProductList(){
	$("#applied-product-block").show();
	$("#applied-overview-block").hide();
	FilterAppliedProductsDataTable();
}