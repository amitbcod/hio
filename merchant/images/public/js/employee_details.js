$(document).ready(function() {

  $('.validate-char').on('keypress', function(key) {
        //alert(111111)
		if((key.charCode < 97 || key.charCode > 122) && (key.charCode < 65 || key.charCode > 90) && (key.charCode != 45 && key.charCode != 32 && key.charCode != 0)) {
			return false;	
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
});
$('.validate-char').on('keypress', function(key) {
        //alert(111111)
		if((key.charCode < 97 || key.charCode > 122) && (key.charCode < 65 || key.charCode > 90) && (key.charCode != 45 && key.charCode != 32 && key.charCode != 0)) {
			return false;	
		}
	});
$('#emp_change_password').validate({
	 rules: {
	  new_password: {
                required: true,
                minlength: 8,
				mypassword: true
            },
			con_new_password: {
				required :true,
				equalTo:   "#new_password"
			}
        },
	messages: {
           
            con_new_password: { "equalTo": "The passwords does not match" }
        }
	
	
	
});	
$('#employee_details').validate({
	 rules: {
            emp_email: {
                required: true,
                //email: true,
                validateEmail: true
            },
            emp_password: {
                required: true,
                minlength: 8,
				mypassword: true
            }
			
			
        },
	messages: {
            emp_password: { "required": "Please enter 8 or more characters." }
			
        }
	
});	

$('#employee_details').on('submit',function(e){
e.preventDefault();
				if($(this).valid()) 
				{
					var formData = new FormData($("#employee_details")[0]);
					$.ajax({
					type:"POST",
					url:$(this).attr('action'),
					dataType:"json",
					data:formData,
					processData: false,
					contentType: false,
					beforeSend:function()
					{
						console.log("Before Send");
						//$("#add_submit").prop("disabled",true).css({"background":"#868686","color":"#fff"});
					},
					success:function(response){
						
						if( response.flag == 1)
						{
							swal({
							title: "",
							icon: "success",
							text: response.msg,
							buttons: false,						
							})
							
							setTimeout(function() {
							   location.reload();

							}, 1000);
							
							
						}

						else
						{
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

$('#emp_change_password').on('submit',function(e){
	e.preventDefault();
if($(this).valid()) {
			var formData = new FormData($("#emp_change_password")[0]);
				$.ajax({
					type:"POST",
					url:$(this).attr('action'),
					dataType:"json",
					data:formData,
					processData: false,
					contentType: false,
					beforeSend:function()
					{
						//$("#add_submit").prop("disabled",true).css({"background":"#868686","color":"#fff"});
					},
					success:function(response){
						if( response.flag == 1)
						{
							swal({ title: "",icon: "success",text: response.msg,button: false},
							function(){location.reload(); })
						}
						else if(response.status == '202')
						{
							$("#chenge_pass_modal").modal('show');
							$("#old_password").focus();		
							$("#old_password").focus(function() {
								$(this).next( "div" ).addClass("error")
								$(this).next( "div" ).html("Old Password Does't match")
								

							});
							// alert("Old Password Does't match");
						}
						else
						{
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


$('#change_email').on('submit',function(e){
	e.preventDefault();
if($(this).valid()) {
			var formData = new FormData($("#change_email")[0]);
				
				$.ajax({
					type:"POST",
					url:$(this).attr('action'),
					dataType:"json",
					data:formData,
					processData: false,
					contentType: false,
					beforeSend:function()
					{
						//$("#add_submit").prop("disabled",true).css({"background":"#868686","color":"#fff"});
					},
					success:function(response){
						if(response.flag == 1)
						{
							swal({ title: "",icon: "success",text: response.msg,button: false},
							function(){location.reload(); })
						}
						// else if(response.status == '202')
						// {
						// 	$("#change_email_modal").modal('show');
						// 	$("#current_email").focus();		
						// 	$("#current_email").focus(function() {
						// 		$(this).next( "div" ).addClass("error")
						// 		$(this).next( "div" ).html("Current Email Does't match")
								

						// 	},function(){location.reload(); });
						// 	// alert("Old Password Does't match");
						// }
						else
						{
							swal({
								title: "",
								icon: "error",
								text: response.msg,
								buttons: false,						
							},function(){location.reload(); })
						}
					}
				});
	}
});
