$(document).ready(function() {	

	$("#dob").datepicker({ 
        autoclose: true, 
        todayHighlight: true,
        startDate: new Date(1970, 0, 1),
        format:'dd-mm-yyyy',
      });
	
	
	$('#edit_customer_form').validate({ 
		rules: {
			first_name: {
				required: true,
			},
			last_name: {
				required: true,
			},
			country: {
				required: true,
			},
			GST_no : {
				required: true,
			},
			company_name : {
				required : true,
			},	
			mobile_no: {
				validate_phone:true
			}
		},messages: {
            password: { "minlength": "Please enter 8 or more characters." }
        }
	});	

	$.validator.addMethod("validate_phone", function(phone_number, element) {
    	    phone_number = phone_number.replace(/\s+/g, "");
    	    return this.optional(element) || phone_number.length > 7 && 
    	    phone_number.match(/^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/);
    	}, "Enter Valid Number.");
		
	
	$('#edit_customer_form').on('submit',function(e){
		e.preventDefault();
		if($(this).valid()) {
			var fd = new FormData($('#edit_customer_form')[0]);
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
                    if (response.flag == 1) {
                    	 $('#FBCUserSecondaryModal').modal('hide');
                        swal({
							title: "",
							icon: "success",
							text: response.msg,
							buttons: false,	
						},
						function(){window.location = response.redirect })

                    } else {
						// grecaptcha.reset();
						 $('#FBCUserSecondaryModal').modal('hide');
                        swal({
							title: "",
							icon: "error",
							text: response.msg,
							buttons: false,		
						},
						 function(){location.reload(); })
                    }
                }
            });
		}
	});
});