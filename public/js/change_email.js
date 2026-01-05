$(document).ready(function() {
	
	$.validator.addMethod('validateEmail', function(value, element) {
        return this.optional(element) || (value.match(/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i));
    },
    'Please enter valid email address.');

	$('#change-email-form').validate({
	 rules: {
            new_email: {
                required: true,
                validateEmail: true,
            }	
        }
});	
	$('#change-email-form').on('submit',function(e){
		e.preventDefault();
		if($(this).valid()) {
			var fd = new FormData($('#change-email-form')[0]);
            $.ajax({
                url: $(this).attr('action'),
                type: 'ajax',
                method: $(this).attr('method'),
                dataType: 'json',
                data: fd,
				processData: false,
				contentType: false,
                success: function(response) {
					$('#ajax-spinner').hide();
                    if(response.flag == 1) {
                    	$("#WebShopCommonModal").modal('hide');
                        swal({
							title: "",
							icon: "success",
							text: response.msg,
							buttons: false,
						})
                        setTimeout(function() {
                            location.reload();
                        }, 1000);

                    }else if(response.flag == 2){
                    		$("#error").html('');
                            $('#error').show();
                    		$('#error').html(response.msg).fadeOut(5000);

                    }else{
                    	$("#WebShopCommonModal").modal('hide');
                    	swal({
							title: "",
							icon: "error",
							text: response.msg,
							buttons: false,
						})
					}
                }
            });
		}
	});
});