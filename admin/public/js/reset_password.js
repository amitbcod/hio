console.log('loaded');
$(document).ready(function() {	
	$(document).on('click', '.toggle-password', function() {
        $(this).toggleClass("eye-text eye-password");

        var input = $("#inputPassword");
        input.attr('type') === 'password' ? input.attr('type', 'text') : input.attr('type', 'password')
    });
	
	$(document).on('click', '.toggle-Confpassword', function() {

        $(this).toggleClass("eye-text eye-password");

        var input = $("#inputConfPassword");
        input.attr('type') === 'password' ? input.attr('type', 'text') : input.attr('type', 'password')
    });
	
	$("#inputPassword").focus(function(){
		$("#message").show();
	});
	
	$("#inputPassword").blur(function(){
		$("#message").hide();
	});
	
	$('#inputPassword').keyup(function(){
		var str = this.value;
		var alphabetic = $("#alphabetic");
		var special = $("#special");
		var number = $("#number");
		var length = $("#length");
		
		var alphabetChar = new RegExp('[a-zA-Z]');
		var numberChar =new RegExp('[0-9]');
		var specialChar = new RegExp('[!@#$%^&*():;?_~+=]');
		
		if(str.match(alphabetChar)){
			alphabetic.removeClass('invalid');
			alphabetic.addClass('valid');
		}else{
			alphabetic.removeClass('valid');
			alphabetic.addClass('invalid');
		}
		
		if(str.match(specialChar)){
			special.removeClass('invalid');
			special.addClass('valid');
		}else{
			special.removeClass('valid');
			special.addClass('invalid');
		}
		
		if(str.match(numberChar)){
			number.removeClass('invalid');
			number.addClass('valid');
		}else{
			number.removeClass('valid');
			number.addClass('invalid');
		}
		
		// Validate length
		if(str.length >= 8){
			length.removeClass('invalid');
			length.addClass('valid');
		}else{
			length.removeClass('valid');
			length.addClass('invalid');
		}
	});
	
	$.validator.addMethod('mypassword', function(value, element) {
        return this.optional(element) || (value.match(/[a-zA-Z]/) && value.match(/[0-9]/) && value.match(/[!@#$%^&*():;?_~+=]/));
    },
    'Password must contain at least one alphabetic, one numeric and one special character.');
	
	$.validator.addMethod('validateEmail', function(value, element) {
        return this.optional(element) || (value.match(/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i));
    },
    'Please enter valid email address.');

    $(".validate-number").keydown(function(event) {


        if (event.shiftKey == true) {
            event.preventDefault();
        }

        if ((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) {

        } else {
            event.preventDefault();
        }

        if ($(this).val().indexOf('.') !== -1 && event.keyCode == 190)
            event.preventDefault();

    });
    
    $('.validate-char').on('keypress', function(key) {
        //alert(111111)
		if((key.charCode < 97 || key.charCode > 122) && (key.charCode < 65 || key.charCode > 90) && (key.charCode != 45 && key.charCode != 32 && key.charCode != 0)) {
			return false;	
		}
	});
	$("#reset-password").validate({
        ignore: ':hidden',		
        //ignore: ".ignore",
        rules: {
            inputPassword: {
                required: true,
                minlength: 8,
				mypassword: true
            },
			inputConfPassword: {
                equalTo: "#inputPassword"
            },
        },
		messages: {
            inputPassword: { "minlength": "Please enter 8 or more characters." },
            inputConfPassword: "Confirm Password does not match." 
        },
		beforeSend: function(){
			$('#ajax-spinner').show();
		},
        submitHandler: function(form) {
			
			var fd = new FormData($('#reset-password')[0]);
			
            $.ajax({
                url: form.action,
                type: 'ajax',
                method: form.method,
                dataType: 'json',
                data: fd,
				processData: false,
				contentType: false,
                success: function(response) {
                    console.log(response);
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
						
                        setTimeout(function() {
                            window.location.href = response.redirect;

                        }, 1000);
                    }
                }
            });
        }
    });
});