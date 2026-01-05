$(document).ready(function() {	

	if($('#country').val() == 'IN'){
		$('.state_div').hide();
		$('.dp_state_div').show();
		$("#state_dp").attr("required", "true");
		$("#state").attr("required", "false");
	}else{
		$('.state_div').show();
		$('.dp_state_div').hide();
		$("#state").attr("required", "true");
		$("#state_dp").attr("required", "false");
	}

	$("#country").change(function() {
	 if(this.value != 'IN') 
	 	{
			$('.dp_state_div').hide();
			$('.state_div').show();
			$("#state").attr("required", "true");
			$("#state_dp").attr("required", "false");
		}else{
			$('.state_div').hide();
			$('.dp_state_div').show();
			$("#state_dp").attr("required", "true");
			$("#state").attr("required", "false");
		}
	});

	$('#edit_customer_address_form').validate({ 
		rules: {
			first_name: {
				required: true,
			},
			last_name: {
				required: true,
			},
			address_line1: {
				required: true,
			},
			city: {
				required: true,
			},
			country: {
				required: true,
			},		
			pincode: {
				required: true,
				//number: true,
				InZip: true
			}
		},messages: {
            password: { "minlength": "Please enter 8 or more characters." }
        }
	});
	
	$.validator.addMethod("InZip", function(value, element) {
	    var isIN = $("#country").val() === "IN";
        var pincodeval = $("#pincode").val();

	    if ( ( isIN && (value.length < 6 || value.length > 6 || !/^[0-9]+$/.test(pincodeval)) )) {
	        return false;
	    } else return true;

	}, "Please enter 6 digits.");


	$('#edit_customer_address_form').on('submit',function(e){
		e.preventDefault();
		if($(this).valid()) {
			// var flag = $("#flag").val();
			var fd = new FormData($('#edit_customer_address_form')[0]);
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