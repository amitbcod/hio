$(document).ready(function() {	

	// if($('#country').val() == 'IN'){
	// 	$('.state_div').hide();
	// 	$('.dp_state_div').show();
	// 	$("#state_dp").attr("required", "true");
	// 	$("#state").attr("required", "false");
	// }else{
	// 	$('.state_div').show();
	// 	$('.dp_state_div').hide();
	// 	$("#state").attr("required", "true");
	// 	$("#state_dp").attr("required", "false");
	// }

	// $("#country").change(function() {
	//  if(this.value != 'IN') 
	//  	{
	// 		$('.dp_state_div').hide();
	// 		$('.state_div').show();
	// 		$("#state").attr("required", "true");
	// 		$("#state_dp").attr("required", "false");
	// 	}else{
	// 		$('.state_div').hide();
	// 		$('.dp_state_div').show();
	// 		$("#state_dp").attr("required", "true");
	// 		$("#state").attr("required", "false");
	// 	}
	// });
	
	$("#dob").datepicker({ 
        autoclose: true, 
        todayHighlight: true,
        startDate: new Date(1970, 0, 1),
        format:'dd-mm-yyyy',
      });
	
	// $.validator.addMethod('mypassword', function(value, element) {
 //        return this.optional(element) || (value.match(/[a-zA-Z]/) && value.match(/[0-9]/) && value.match(/[!@#$%^&*():;?_~+=]/));
 //    },
 //    'Password must contain at least one alphabetic, one numeric and one special character.');
	
	// $.validator.addMethod('validateEmail', function(value, element) {
 //        return this.optional(element) || (value.match(/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i));
 //    },
 //    'Please enter valid email address.');

	$('#edit_customer_form').validate({ 
		rules: {
			first_name: {
				required: true,
			},
			last_name: {
				required: true,
			},
			// email: {
			// 	required: true,
			// 	validateEmail: true
			// },
			// password: {
			// 	required: true,
			// 	minlength: 8,
			// 	mypassword: true
			// },
			// address_line1: {
			// 	required: true,
			// },
			// city: {
			// 	required: true,
			// },
			country: {
				required: true,
			},
			GST_no : {
				required: true,
			},
			company_name : {
				required : true,
			},			
			// pincode: {
			// 	required: true,
			// 	number: true,
			// 	minlength: 6,
			// 	maxlength: 6	
			// },
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
			// var flag = $("#flag").val();
			var fd = new FormData($('#edit_customer_form')[0]);
			// fd.append('flag',flag);
            $.ajax({
                url: $(this).attr('action'),
                type: 'ajax',
                method: $(this).attr('method'),
                dataType: 'json',
                data: fd,
				processData: false,
				contentType: false,
                success: function(response) {
                    // console.log(response);
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