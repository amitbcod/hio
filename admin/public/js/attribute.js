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
			beforeSend: function(){
				$('#ajax-spinner').show();
		   },
			success: function(response) {
				window.setTimeout( function() {
					$('#ajax-spinner').hide();
				},1500);
				
				if(response == "insert"){
					$('#attributebtn').attr('disabled', 'disabled');
					swal('Success','Attribute Insert successfully!','success');
						window.setTimeout( function() {
						window.location.href="/admin/attribute";
						}, 1500);
				}else if (response == "update") {
					$('#attributebtn').attr('disabled', 'disabled');
					swal('Success','Attribute Update successfully!','success');
						window.setTimeout( function() {
						window.location.href="/admin/attribute";
						}, 1500);

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