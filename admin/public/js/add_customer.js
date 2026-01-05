$(document).ready(function() {

	if($('#country').val() == 'IN'){
		$('.state_div').hide();
		$('.dp_state_div').show();
		$("#state_dp").attr("required", "true");
		$("#state").removeAttr("required");
	}else if($('#country').val() == 'US' || $('#country').val() == 'CA' || $('#country').val() == 'AU'){
		$('.state_div').show();
		$('.dp_state_div').hide();
		$("#state_dp").removeAttr("required");
		$("#state").attr("required", "true");
	}else if($('#country').val() == 'BE' || $('#country').val() == 'DE' || $('#country').val() == 'FR'){
		$('.state_div').hide();
		$('.dp_state_div').hide();
		$("#state_dp").removeAttr("required");
		$("#state").removeAttr("required");
	}else{
		$('.state_div').show();
		$('.dp_state_div').hide();
		$("#state").removeAttr("required");
		$("#state_dp").removeAttr("required");
	}

	$("#country").change(function() {
		if(this.value == 'IN')
		{
			$('.state_div').hide();
			$('.dp_state_div').show();
			$("#state_dp").attr("required", "true");
			$("#state").removeAttr("required");
	   }else if(this.value == 'US' || this.value == 'CA' || this.value == 'AU'){
			$('.state_div').show();
			$('.dp_state_div').hide();
			$("#state_dp").removeAttr("required");
			$("#state").attr("required", "true");
		}else if(this.value == 'BE' || this.value == 'DE' || this.value == 'FR'){
			$('.state_div').hide();
			$('.dp_state_div').hide();
			$("#state_dp").removeAttr("required");
			$("#state").removeAttr("required");
		}else{
			$('.state_div').show();
			$('.dp_state_div').hide();
			$("#state").removeAttr("required");
			$("#state_dp").removeAttr("required");
	   }
	});

	CheckAddressValMax($('#country').val(),'#address_line1','#address_line2');

	$("#country").change(function() {
		CheckAddressValMax($('#country').val(),'#address_line1','#address_line2');
	});

	$("#dob").datepicker({
        autoclose: true,
        todayHighlight: true,
        startDate: new Date(1970, 0, 1),
        format:'dd-mm-yyyy',
      });

	$.validator.addMethod('mypassword', function(value, element) {
        return this.optional(element) || (value.match(/[a-zA-Z]/) && value.match(/[0-9]/) && value.match(/[!@#$%^&*():;?_~+=]/));
    },
    'Password must contain at least one alphabetic, one numeric and one special character.');

	$.validator.addMethod('validateEmail', function(value, element) {
        return this.optional(element) || (value.match(/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i));
    },
    'Please enter valid email address.');

	$('#add_customer_form').validate({
		rules: {
			first_name: {
				required: true,
			},
			last_name: {
				required: true,
			},
			email: {
				required: true,
				validateEmail: true
			},
			customer_type_id:{
				required: true,
			},
			password: {
				required: true,
				minlength: 8,
				mypassword: true
			},
			address_line1: {
				required: true,
				
			},
			address_line2: {
				ValMaxCharCheck	: true,
			},
			
			gender:{
				required: true,
			},
			city: {
				required: true,
			},
			state:{
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
			pincode: {
				required: true,
				//number: true,
				InZip: true
			},
			mobile_no: {
				required: true,
				validate_phone:true
			}
		},messages: {
            password: { "minlength": "Please enter 8 or more characters." }
        }
	});

	$.validator.addMethod("InZip", function(value, element) {
		var country = $("#country").val();
		return checkPinBaseOnCountry(value,country);
	},"Invalid Pin code");

	$.validator.addMethod("validate_phone", function(phone_number, element) {
    	    phone_number = phone_number.replace(/\s+/g, "");
    	    return this.optional(element) || phone_number.length > 7 &&
    	    phone_number.match(/^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/);
    	}, "Enter Valid Number.");

	var address_validation_mssg = '';
	var address_mssg = function (){
		return address_validation_mssg;
	}

	$.validator.addMethod("ValMaxCharCheck", function(value, element) {
		var country = $("#country").val();

		if (country == 'IN' && value.length > 150) {
			address_validation_mssg = 'Please enter no more than 150 characters.';
			return false;
		}
		else if(country != 'IN' && value.length > 150){
			address_validation_mssg = 'Please enter no more than 150 characters.';
			return false;
		}
		else return true;

	}, address_mssg );

	$('#add_customer_form').on('submit',function(e){
		e.preventDefault();
		if($(this).valid()) {
			// var flag = $("#flag").val();
			var fd = new FormData($('#add_customer_form')[0]);
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

                        swal({
							title: "",
							icon: "success",
							text: response.msg,
							buttons: false,
						},
						function(){window.location = response.redirect })

                    } else {
						// grecaptcha.reset();
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
