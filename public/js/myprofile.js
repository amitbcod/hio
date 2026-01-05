console.log('loaded');

$(document).on('load', function(e) {



	$('.day').val();

    console.log('loaded.......');

});

$(document).ready(function() {



	

	$.validator.addMethod('mypassword', function(value, element) {

        return this.optional(element) || (value.match(/[a-zA-Z]/) && value.match(/[0-9]/) && value.match(/[!@#$%^&*():;?_~+=]/));

    },

    'Password must contain at least one alphabetic, one numeric and one special character.');



	$.validator.addMethod('validateEmail', function(value, element) {

        return this.optional(element) || (value.match(/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i));

    },

    'Please enter valid email address.');



	var date = new Date();



	$("#dob").dateDropdowns({

	// The format of the date string provided to defaultDate

		defaultDateFormat:"yyyy-mm-dd",

	// The lowest year option that will ba available.

	minYear:"1950",

	// The highest year option that will be available.

	maxYear:"2009",

	// Specify the name attribute for the hidden field that will contain the formatted date for submission.

	submitFieldName: "dob",

	// Specify a classname to add to the widget wrapper.

	wrapperClass: "date-dropdowns",

	// Set custom classes on generated dropdown elements

	dropdownClass: null,

	});



	$("#customer-personal-info-form").validate({

        ignore: ':hidden',

        rules: {

            first_name: {

                required: true,

				lettersonly: true,

            },

            last_name: {

                required: true,

				lettersonly: true,

            },

            mobile_no: {

				  validate_phone:true

			}

        },

		beforeSend: function(){

			$('#ajax-spinner').show();

		},

        submitHandler: function(form) {



			var fd = new FormData($('#customer-personal-info-form')[0]);



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

                            location.reload();

                        }, 1000);



                    } else {

						if(CAPTCHA_CHECK_FLAG =='yes') {

							grecaptcha.reset();

						}

                        swal({

							title: "",

							icon: "error",

							text: response.msg,

							buttons: false,

							timer: 3000

						})



                        

                    }

                }

            });

        }

    });



     $.validator.addMethod("validate_phone", function(phone_number, element) {

    	    phone_number = phone_number.replace(/\s+/g, "");

    	    return this.optional(element) || phone_number.length <= 10 &&

    	    phone_number.match(/^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/);

    	}, "Please specify a valid phone number.");



		$.extend($.validator.messages, { lettersonly: "Alphabetic characters only please" })



	$(".make-default").on('click', function() {

		var address_id = $(this).attr("data-address-id");

		if(address_id != ''){

			$.ajax({

				type: "POST",

				dataType: "json",

				url: BASE_URL+"MyProfileController/makeDefaultAddress",

				data: {address_id:address_id},

				complete: function () {

				},

				beforeSend: function(){

				},

				success: function(response) {

					if (response.flag == 1) {

                        swal({

							title: "",

							icon: "success",

							text: response.msg,

							buttons: false,

						})



                        setTimeout(function() {

                            location.reload();

                        }, 1000);



                    } else {

						if(CAPTCHA_CHECK_FLAG =='yes') {

							grecaptcha.reset();

						}

                        swal({

							title: "",

							icon: "error",

							text: response.msg,

							buttons: false,

							timer: 3000

						})

                    }

				}

			});

		}else{

			return false;

		}

	})



	$(".remove-address").on('click', function() {



		swal({

            title: "Are you sure ?",

            text: "You want to delete your address", 

            icon: "warning",

            buttons: true,

            dangerMode: true,

        })

        .then((willDelete) => {

          if (willDelete) {

				var address_id = $(this).attr("data-address-id");

				if(address_id != ''){

				$.ajax({

					type: "POST",

					dataType: "json",

					url: BASE_URL+"MyProfileController/removeAddress",

					data: {address_id:address_id},

					//async:false,

					complete: function () {

					},

					beforeSend: function(){

					},

					success: function(response) {

						if (response.flag == 1) {

							swal({

								title: "",

								icon: "success",

								text: response.msg,

								buttons: false,

							})



							setTimeout(function() {

								location.reload();

							}, 1000);



						} else {

							if(CAPTCHA_CHECK_FLAG =='yes') {

								grecaptcha.reset();

							}

							swal({

								title: "",

								icon: "error",

								text: response.msg,

								buttons: false,

								timer: 3000

							})

						}

					}

				});

			}else{

				return false;

			}

          } else {

            //swal("Your imaginary file is safe!");

          }

        });

		var address_id = $(this).attr("data-address-id");

		

	})



	

	$("#return-order").validate({

        ignore: ':hidden',

        rules: {

            reason_for_return: {

                required: true,

            },

			refund_payment_mode: {

                required: true,

            },

			bank_name:{



				 required:$('input:radio[name=refund_payment_mode]:checked').val()=='2'

			},

			bank_branch:{

				required:$('input:radio[name=refund_payment_mode]:checked').val()=='2'

			},

			bank_ifsc:{



				 required:$('input:radio[name=refund_payment_mode]:checked').val()=='2'

			},

			bic_swift:{

				required:$('input:radio[name=refund_payment_mode]:checked').val()=='2'

			},

			bank_acc_no: {

				required:$('input:radio[name=refund_payment_mode]:checked').val()=='2'

			}



        },

        messages: {



        },

		beforeSend: function(){

			$('#ajax-spinner').show();

		},

        submitHandler: function(form) {



			var fd = new FormData($('#return-order')[0]);

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

                           location.reload();



                        }, 1000);



                    } else {



                        swal({

							title: "",

							icon: "error",

							text: response.msg,

							buttons: false,

						});



                    }

                }

            });

        }

    });



	$('input[type=radio][name=refund_payment_mode]').change(function() {

		var cur_value=$(this).val();

		if(cur_value==1){

			$('#bank-details-div').hide();

		}else{



			$('#bank-details-div').show();

		}



	});


	$("#state_dp").on("change", function () {
		var stateId = $(this).val();

		// Reset city dropdown
		$("#city").empty().append('<option value="">Select City</option>');

		if (stateId != "") {
			$.ajax({
				url: base_url + "MyProfileController/getCities", // Your controller function
				type: "POST",
				data: { state_id: stateId },
				dataType: "json",
				success: function (res) {
					if (res.status == "success" && res.cities.length > 0) {
						$.each(res.cities, function (i, city) {
							$("#city").append(
								'<option value="' + city.id + '">' + city.city_name + '</option>'
							);
						});
					}
				},
			});
		}
	});


});



function openChangePasswordPopup(customer_id){

	if(customer_id != ''){

		$.ajax({

			type: "POST",

			dataType: "html",

			url: BASE_URL+"MyProfileController/changePassword",

		

			complete: function () {

			},

			beforeSend: function(){

			},

			success: function(response) {

				$("#WebShopCommonModal").modal();

				$("#modal-content").html(response);

			}

		});

	}else{

		return false;

	}

}



$('#cancel-order-form').submit(function(e){



	e.preventDefault();

		var fd = new FormData($('#cancel-order-form')[0]);

		var order_id = (fd.get('order_id'));



		if(order_id !=''){

			$.ajax({

			type: "POST",

			dataType: "html",

			url: BASE_URL+"MyOrdersController/cancelOrder",

			data: fd,

			processData: false,

			contentType: false,

			beforeSend: function(){

			},

			success: function(response) {

				 console.log(response);

				 var response1= JSON.parse(response);

                    if (response1.flag == 1) {

                    	$('#cancel-order-modal').modal('hide');

                        swal({

							title: "",

							icon: "success",

							text: response1.msg,

						

						}).then(function(){

						   location.reload();

						   }

						);



                    } else {

                        swal({

							title: "",

							icon: "error",

							text: response1.msg,

						

						}).then(function(){

						   location.reload();

						   }

						);



                    }

				},

				error : function(error)

				{

					console.log(error);

				}

			});

		}else{

			return false;

		}





});





$('#cancel-guest-order-form').submit(function(e){



	e.preventDefault();

		var fd = new FormData($('#cancel-guest-order-form')[0]);

		var order_id = (fd.get('order_id'));



		if(order_id !=''){

			$.ajax({

			type: "POST",

			dataType: "html",

			url: BASE_URL+"MyGuestOrdersController/cancelOrder",

			data: fd,

			processData: false,

			contentType: false,

			beforeSend: function(){

			},

			success: function(response) {

				 console.log(response);

				 var response1= JSON.parse(response);

                    if (response1.flag == 1) {

                    	$('#cancel-order-modal').modal('hide');

                        swal({

							title: "",

							icon: "success",

							text: response1.msg,

							

						}).then(function(){

						   location.reload();

						   }

						);



                    } else {

						

                        swal({

							title: "",

							icon: "error",

							text: response1.msg,

							

						}).then(function(){

						   location.reload();

						   }

						);



                        

                    }

				},

				error : function(error)

				{

					console.log(error);

				}

			});

		}else{

			return false;

		}





});



function openAddressPopup(flag,customer_id,address_id=''){

	if(flag!= '' && customer_id != ''){

		$.ajax({

			type: "POST",

			dataType: "html",

			url: BASE_URL+"MyProfileController/openAddressPopup",

			data: {flag:flag,customer_id:customer_id,address_id:address_id},

		

			complete: function () {

			},

			beforeSend: function(){

			

			},

			success: function(response) {

				$("#WebShopCommonModal").modal();

				$("#modal-content").html(response);

			}

		});

	}else{

		return false;

	}

}



function openChangeEmailPopup(customer_id){

	if(customer_id != ''){

		$.ajax({

			type: "POST",

			dataType: "html",

			url: BASE_URL+"MyProfileController/changeEmail",

		

			complete: function () {

			},

			beforeSend: function(){

			},

			success: function(response) {

				$("#WebShopCommonModal").modal();

				$("#modal-content").html(response);

			}

		});

	}else{

		return false;

	}

}

