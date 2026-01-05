$("#attributeForm").validate({
	rules: {
		attribute_name: {required: true,},
		attribute_code: {required: true },
		attribute_properties: {required: true}
	},
	submitHandler: function(form) {
		var formData = new FormData($('#attributeForm')[0]);
		$.ajax({
			url: form.action,
			type: 'ajax',
			method: form.method,
			dataType: 'json',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				if (response.flag == 1) {
					swal({ title: "",text: response.msg, button: false, icon: 'success' })
					setTimeout(function() { window.location.href = response.url;}, 1500);
				}else{
					swal({ title: "",text: response.msg, button: false, icon: 'success' })
					return false;
				}
			},
			error: function (response) {
				console.log(response.responseText);
				return false;
			}
		});
		return false;
	}
});

$(document).ready(function(){
	// Destroy all previous bootstrap tags inputs (optional)
			$( "#attribute_properties" ).change(function() {
				  if(this.value=='5' || this.value=='6'){
					  $('#slectvalue').show();
				  }else{
					   $('#slectvalue').hide();
					   $( "#tagsinput" ).val('');
					   $('input[data-role="tagsinput"]').tagsinput('destroy');
				  }
			});
	});