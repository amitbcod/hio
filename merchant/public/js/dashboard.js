$(document).ready(function() {	
	if($('#bill_country').val() == 'IN'){
		$('.bstate_div').hide();
		$('.dp_bstate_div').show();
	}else{
		$('.bstate_div').show();
		$('.dp_bstate_div').hide();
	}

	$("#bill_country").change(function() {
	 if(this.value != 'IN') 
	 	{
			$('.dp_bstate_div').hide();
			$('.bstate_div').show();
		}else{
			$('.bstate_div').hide();
			$('.dp_bstate_div').show();
		}
	});

	if($('#ship_country').val() == 'IN'){
		$('.sstate_div').hide();
		$('.dp_sstate_div').show();
	}else{
		$('.sstate_div').show();
		$('.dp_sstate_div').hide();
	}

	$("#ship_country").change(function() {
	 if(this.value != 'IN') 
	 	{
			$('.dp_sstate_div').hide();
			$('.sstate_div').show();
		}else{
			$('.sstate_div').hide();
			$('.dp_sstate_div').show();
		}
	});

   var table1 = $('#employee_details_table').DataTable({"ordering": false, "stateSave": true, "paging": true,"searching": true,"jQueryUI": false,"dom" : '<"top"tp>'});
	$.fn.dataTable.ext.errMode = 'none';
	
			$('#emp_search').on('keyup change', function () 
			{
				console.log(this.value);
			    table1.search(this.value);
			    table1.draw();
			    // var dlr_count = table1.page.info();
			    
			});
			$('#page_length').change( function() { 
			table1.page.len( $(this).val() ).draw();
		});
		
	  $('.validate-char').on('keypress', function(key) {
        //alert(111111)
		if((key.charCode < 97 || key.charCode > 122) && (key.charCode < 65 || key.charCode > 90) && (key.charCode != 45 && key.charCode != 32 && key.charCode != 0)) {
			return false;	
		}
	});	
	
	
	$.validator.addMethod(
			"regex_pcname",
			function(value, element, regexp) {
				var re = new RegExp(regexp);
				return this.optional(element) || re.test(value);
			},
			"Only allowed numbers(0-9), letters(a-z A-Z)."
	);
	
	$.validator.addMethod(
			"regex_gst",
			function(value, element, regexp) {
				var re = new RegExp(regexp);
				return this.optional(element) || re.test(value);
			},
			"Please enter valid GST number."
	);
	
	$.validator.addMethod(
			"validate_mobile",
			function(value, element, regexp) {
				var re = new RegExp(regexp);
				return this.optional(element) || re.test(value);
			},
			"Please enter valid mobile number."
	);
	
	$.validator.addMethod(
			"regex_url",
			function(value, element, regexp) {
				var re = new RegExp(regexp);
				return this.optional(element) || re.test(value);
			},
			"Please enter valid website url like https://example.com OR https://www.example.com"
	);
	
	$("#bill_pincode").rules("add", { regex_pcname: "^[a-zA-Z0-9]+$" });
	$("#ship_pincode").rules("add", { regex_pcname: "^[a-zA-Z0-9]+$" });
	//$("#gst_number").rules("add", { regex_gst: "^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]1}[1-9A-Z]{1}Z[0-9A-Z]{1}$" });
	$("#gst_number").rules("add", { regex_gst: "^[a-zA-Z0-9]+$" });
	
	//$("#website_address").rules("add", { regex_url: "^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=.]+$" });


	$("#mobile_no").rules("add", { validate_mobile: "^[0-9-+]+$" });

	
	if ($('#employee_details_table tr').length < 8) {
	
            $('.dataTables_paginate').hide();
        }

$.validator.addMethod('mypassword', function(value, element) {
        return this.optional(element) || (value.match(/[a-zA-Z]/) && value.match(/[0-9]/) && value.match(/[!@#$%^&*():;?_~+=]/));
    },
    'Password must contain at least one alphabetic, one numeric and one special character.');
	$.validator.addMethod('validateEmail', function(value, element) {
        return this.optional(element) || (value.match(/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i));
    },
    'Please enter valid email address.');
});
	$("#change_password").change(function() {
	 if(this.checked) {
		$("#password_change_div").css("display","block");
	}
	else
	{
		$("#password_change_div").css("display","none");

	}
	
	});
	
	$('.validate-char').on('keypress', function(key) {
        //alert(111111)
		if((key.charCode < 97 || key.charCode > 122) && (key.charCode < 65 || key.charCode > 90) && (key.charCode != 45 && key.charCode != 32 && key.charCode != 0)) {
			return false;	
		}
	});
	
	
	
	
$('#change_password').validate({
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
$('#user_details').validate({
	 rules: {
            email: {
                required: true,
                //email: true,
                validateEmail: true
            },
            new_password: {
                required: true,
                minlength: 8,
				mypassword: true
            }
			
        },
	messages: {
            emp_password: { "required": "Please enter 8 or more characters." },
            
        }
	
	
	
});	

$('#user_details').on('submit',function(e){
	e.preventDefault();
if($(this).valid()) {
			var formData = new FormData($("#user_details")[0]);
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
						if( response.msg == "Success")
						{
							swal({
								title: "",
								icon: "success",
								text: response.msg,
								buttons: false,						
							},
                        function() {location.reload(); })
							// location.reload();
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

$('#change_password').on('submit',function(e){
	e.preventDefault();
if($(this).valid()) {
			var formData = new FormData($("#change_password")[0]);
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
						if( response.msg == "Success")
						{
							swal({
								title: "",
								icon: "success",
								text: response.msg,
								buttons: false,
								timer:3000								
							})
							 setTimeout(function() {
								location.reload();
                        }, 1000);
							// location.reload();
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

function change_employee_status(action,fbc_usr_id)
{
	var base_url = $("#base").val();
	if(action == 'D')
	{
		var status = 2;
	}
	else if(action == 'E')
	{
		var status = 1;
	}
	
				$.ajax({
					type:"POST",
					url:'DashboardController/change_employee_status',
					dataType:"json",
					data :{status:status,fbc_usr_id:fbc_usr_id},
					beforeSend:function()
					{
						//$("#add_submit").prop("disabled",true).css({"background":"#868686","color":"#fff"});
					},
					success:function(response){
						if( response.flag == "1")
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
							location.reload();
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

$("#user-edit").validate({
	rules: {
		first_name: 'required',
        last_name: 'required',
        username:'required',
        usertype: 'required',
		cp_radio: 'required'
	 },
	 messages: {
		
	 },
	 submitHandler: function(form) {
		var formData = new FormData($("#user-edit")[0]);

		$.ajax({
			type: "POST",
			url: BASE_URL+"DashboardController/edit_admin_user_details",
			dataType: 'json',
			data: formData,
			cache: false,
			processData: false,
			contentType: false,
			success: function (res) {
			   var dataResult = JSON.parse(JSON.stringify(res));
			   if (dataResult.status == 200) {
				swal({
					title: "",
					icon: "success",
					text: res.msg,
					buttons: false,
				});
				}
				else{
					swal({
						title: "",
						icon: "error",
						text: res.msg,
						buttons: false,
					});
				}
			}
		});
	 }
});

function OpenEmailChangePopup() {
	$.ajax({
		type: "POST",
		dataType: "html",
		url: BASE_URL+"DashboardController/openAdminUserPopup",
		data: {},
		async:false,
		complete: function () { 
		},	
		beforeSend: function(){
			 $('#ajax-spinner').show();
		},			
		success: function(response) {
			 $('#ajax-spinner').hide();
			if(response!='error'){
			
				$("#FBCUserCommonModal").modal();
				$("#modal-content").html(response);
			}else{
				
			}
			
		}
	});
}

$("#change-email").validate({
	rules: {
		current_email: {
		   required: true,
		   email: true,
		   remote:
			{
				url: BASE_URL+"DashboardController/email_exists",
				type: "POST",
				data: {
					user_id: $('#user_id').val(),
				}
			}  
		},
		new_email: {
			required: true,
			email: true,
			remote:
			 {
				 url: BASE_URL+"DashboardController/email_exists",
				 type: "POST",
				 data: {
					 user_id: $('#user_id').val(),
				 }
			 }  
		 }
	 },
	 messages: {
		current_email: 'Email Already in Use',
		new_email: 'Email Already in Use'
	 },
	 submitHandler: function(form) {
		var formData = new FormData($("#change-email")[0]);

		$.ajax({
			type: "POST",
			url: BASE_URL+"DashboardController/edit_admin_user_email",
			dataType: 'json',
			data: formData,
			cache: false,
			processData: false,
			contentType: false,
			success: function (res) {
			   var dataResult = JSON.parse(JSON.stringify(res));
			   if (dataResult.status == 200) {
				swal({
					title: "",
					icon: "success",
					text: res.msg,
					buttons: false,
				});
				$('#FBCUserCommonModal').modal('toggle');
				setTimeout(location.reload(), 5000);
				}
				else{
					swal({
						title: "",
						icon: "error",
						text: res.msg,
						buttons: false,
					});
				}
			}
		});
	 }
});

function OpenPasswordChangePopup() {
	$.ajax({
		type: "POST",
		dataType: "html",
		url: BASE_URL+"DashboardController/openAdminUserPasswordPopup",
		data: {},
		async:false,
		complete: function () { 
		},	
		beforeSend: function(){
			 $('#ajax-spinner').show();
		},			
		success: function(response) {
			 $('#ajax-spinner').hide();
			if(response!='error'){
			
				$("#FBCUserCommonModal").modal();
				$("#modal-content").html(response);
			}else{
				
			}
			
		}
	});
}

$("#change-password").validate({
	rules: {
		current_password: 'required',
        new_password: 'required',
	 },
	 messages: {
		current_password: 'Current Password Field is Required',
		new_password: 'New Password Field is Required'
	 },
	 submitHandler: function(form) {
		var formData = new FormData($("#change-password")[0]);

		$.ajax({
			type: "POST",
			url: BASE_URL+"DashboardController/edit_admin_user_password",
			dataType: 'json',
			data: formData,
			cache: false,
			processData: false,
			contentType: false,
			success: function (res) {
			   var dataResult = JSON.parse(JSON.stringify(res));
			   if (dataResult.status == 200) {
				swal({
					title: "",
					icon: "success",
					text: res.msg,
					buttons: false,
				});
				$('#FBCUserCommonModal').modal('toggle');
				setTimeout(location.reload(), 5000);
				}
				else{
					swal({
						title: "",
						icon: "error",
						text: res.msg,
						buttons: false,
					});
				}
			}
		});
	 }
});