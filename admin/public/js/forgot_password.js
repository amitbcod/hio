console.log('loaded');
$(document).ready(function() {	

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
	
	$("#forgot-password").validate({
        ignore: ':hidden',		
        rules: {
            inputEmail: {
                required: true,
                validateEmail: true
            },
        },
		beforeSend: function(){
			$('#ajax-spinner').show();
		},
        submitHandler: function(form) {
			
			var fd = new FormData($('#forgot-password')[0]);
			
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
                        }, 1000);
                    }
                }
            });
        }
    });
});