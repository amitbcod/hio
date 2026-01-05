$("#orderForm").validate({
	ignore: ':hidden',		
	//ignore: ".ignore",
	// rules: {
		// "qty[]": {
			// //minlength: 1,
			// number: true,
		// },
	// },
	beforeSend: function(){
		$('#ajax-spinner').show();
	},
	submitHandler: function(form) {
		//$('#product_list_next_btn').attr('disabled',true);
		var formData = new FormData($('#orderForm')[0]);
		//formData.append('draft_id', $('#draft_id').val());
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
				//$('#product_list_next_btn').attr('disabled',false);
				//console.log(response);
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