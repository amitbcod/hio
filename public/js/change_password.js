$(document).ready(function() {
	$.validator.addMethod('mypassword', function(value, element) {
        return this.optional(element) || (value.match(/[a-zA-Z]/) && value.match(/[0-9]/) && value.match(/[!@#$%^&*():;?_~+=]/));
    },
    'Password must contain at least one alphabetic, one numeric and one special character.');

	$('#change-password-form').validate({
		rules: {
			old_password: {
				required: true,
			},
			new_password: {
				required: true,
				minlength: 8,
				mypassword: true
			},
			conf_password: {
                equalTo: "#new_password"
            },
		},
		messages: {
            new_password: { "minlength": "Please enter 8 or more characters." },
            conf_password: "Confirm Password does not match."
        },
	});

	$('#change-password-form').on('submit',function(e){
		e.preventDefault();
		if($(this).valid()) {
			var fd = new FormData($('#change-password-form')[0]);

            $.ajax({
                url: $(this).attr('action'),
                type: 'ajax',
                method: $(this).attr('method'),
                dataType: 'json',
                data: fd,
				processData: false,
				contentType: false,
                success: function(response) {
                    console.log(response);
					$('#ajax-spinner').hide();
                    if(response.flag == 1) {
                    	$("#WebShopCommonModal").modal('hide');
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

                    }else{
                    	$("#WebShopCommonModal").modal('hide');
                    	swal({
							title: "",
							icon: "error",
							text: response.msg,
							buttons: false,
							//timer: 3000
						})
					}
                }
            });
		}
	});
});