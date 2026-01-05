$(document).ready(function() {

$("#conatct_order_no").hide();
$("#order_flag").click(function() {
    if($(this).is(":checked")) {
        $("#conatct_order_no").show();
    } else {
        $("#conatct_order_no").hide();
    }
});

   
	$.validator.addMethod('validateEmail', function(value, element) {
        return this.optional(element) || (value.match(/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i));
    },

    'Please enter valid email address.');

	$("#contact-form").validate({
        rules: {
            name: {
                required: true,
            },
			email: {
                required: true,
				validateEmail: true
            },
             order_increment_id: {
                required:{
                    depends: function(element) {
                        return ($('#order_flag').val() !== "")
                    }
                }
            },
			comments: {
                required: true,
            },
        },
        submitHandler: function(form) {
            $('#ajax-spinner').show();
    		var fd = new FormData($('#contact-form')[0]);
            $.ajax({
                url: form.action,
                type: 'ajax',
                method: form.method,
                dataType: 'json',
                data: fd,
				processData: false,
				contentType: false,

                success: function(response) {
					$('#ajax-spinner').hide();
                    if (response.flag == 1) {
                        swal({
							title: "",
							icon: "success",
							text: response.msg,
							buttons: false,
						})

                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1000);
                    } else {
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
