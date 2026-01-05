$(document).ready(function() {
	const session_vat_flag = parseInt($('#session_vat_flag').val() ?? 0);

	var state_div = '.state_div';
	var dp_state_div = '.dp_state_div';
	var state_dp = '#state_dp';
	var state = '#state';

	CheckState($('#country').val(),state_div,dp_state_div,state_dp,state);

	$("#country").change(function() {
		CheckState($('#country').val(),state_div,dp_state_div,state_dp,state);
		var pincode = $('#pincode').val();
		var country = $('#country').val();
		changeCountryBasedOnPostalCode(pincode,country ,'#country');
	});

	CheckAddressValMax($('#country').val(),'#address_line1','#address_line2');

	$("#country").change(function() {
		CheckAddressValMax($('#country').val(),'#address_line1','#address_line2');
	});

	$('#address-form').validate({
		rules: {
			first_name: {
				required: true,
			},
			last_name: {
				required: true,
			},
			address_line1: {
				required: true,
				ValMaxCharCheck	: true,
			},
			address_line2: {
				ValMaxCharCheck	: true,
			},
			city: {
				required: true,
			},
			pincode: {
				required: true,
				Pin_noCheck:true,
				CheckPinCode:true,
			},
			country: {
				required: true,
			},
			mobile_no: {
				required: true,
				validate_phone:true
			},
			vat_no: {
				required: false,
				vat_noCheck: session_vat_flag === 1
			}
		}
	});

	var address_validation_mssg = '';

	var address_mssg = function (){
		return address_validation_mssg;
	}

	$.validator.addMethod("ValMaxCharCheck", function(value, element) {
		var country = $("#country").val();

		if ((country == 'IN' && value.length > 150) || (country == '' && (SHOP_FLAG == 2 || SHOP_FLAG == 1 || SHOP_FLAG == 4) && value.length > 150)) {
			address_validation_mssg = 'Please enter no more than 150 characters.';
			return false;
	    }
		else if(country == '' && (SHOP_FLAG == 2 || SHOP_FLAG == 1 || SHOP_FLAG == 4)){
			return true;
		}
		else if(country != 'IN' && value.length > 35){
			address_validation_mssg = 'Please enter no more than 35 characters.';
			return false;
		}
		else return true;

	}, address_mssg );

	$.validator.addMethod("CheckPinCode", function(value, element) {
		var country = $("#country").val();
		return checkPinBaseOnCountry(value,country);
	},"Invalid Pin code");

	$.validator.addMethod("Pin_noCheck", function(value, element) {
		var country = $("#country").val();
		changeCountryBasedOnPostalCode(value,country,'#country');
		return true;
	},"pin");

	$.validator.addMethod("validate_phone", function(phone_number, element) {
    	    phone_number = phone_number.replace(/\s+/g, "");
    	    return this.optional(element) || phone_number.length >= 7 && phone_number.length <= 15 &&
    	    phone_number.match(/^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/);
    	}, "Please specify a valid phone number.");

		$.validator.addMethod("vat_noCheck",function(val, elem){

		if(val== ''){

			return true;

		}
		if(event.type == "submit"){
			//alert('submit');
			return true;
		} else if(event.type == "blur" ) {
			//alert('blur');
		// if(event.type == "submit" ||  event.type == "blur" ) {
			$("#vat_no-error").remove();
			$('#vat_no-success').remove();
			$('#vatFlag').val("0");
			valid = false;
			$.ajax({
				url: BASE_URL+'CheckoutController/checkvatAlreadyExits',
				type: 'ajax',
				method: 'POST',
				dataType: 'json',
				data: {vat_no:val},
				async: false,
				beforeSend: function(){
					$(".loaderDiv").show();
				},
				success:function(response){

						if(response.flag == 0){


							var vat_no = val;
							var final_res = response.result;

							if(final_res.response_type == 0){

								var url = BASE_URL+'index-vat.php';
									$("#vat_no-error").remove();
									$('#vat_no-success').remove();
									$.ajax({
										url: url,
										type: 'ajax',
										method: 'POST',
										dataType: 'json',
										data: {vat_no:vat_no},
										async: false,
										success:function(response){
											var address_id = $('#address_id').val();
											if (response.flag == 0) {


												if(response.validitiy == 'Valid'){

													$('#consulation_no').val(response.Identifier);
													$('#res_company_name').val(response.Company_name);
													$('#res_company_address').val(response.Company_address);

													addingVatLog(val,response,1,address_id);

													$.validator.messages.vat_noCheck = '';
													$( '<label id="vat_no-success" class="success-msg" for="vat_no"> '+ vat_no + ' is valid. </label>' ).insertAfter( "#vat_no" );
													valid = true;
													$(".loaderDiv").hide();
													$('#vatFlag').val("1");

												}else{

													addingVatLog(val,response,2,address_id);

													$.validator.messages.vat_noCheck = val+" is not valid.";
													valid = false;
													$(".loaderDiv").hide();

												}

											}else{

												addingVatLog(val,response,0,address_id);
												//$.validator.messages.vat_noCheck= 'Vat number checking is not available at the moment. Please try again later.';
												$.validator.messages.vat_noCheck= response.msg+ ". Vat will be charged.";
												// $.validator.messages.vat_noCheck= response;
												valid = false;
												$(".loaderDiv").hide();
											}




										}

									});





							}else if(final_res.response_type == 2){
								$.validator.messages.vat_noCheck = vat_no+" is not valid.";
								valid = false;
								$(".loaderDiv").hide();



							}else if(final_res.response_type == 1){

								$('#consulation_no').val(final_res.consulation_no);
								$('#res_company_name').val(final_res.company_name);
								$('#res_company_address').val(final_res.company_address);

								$.validator.messages.vat_noCheck = "";
								$( '<label id="vat_no-success" class="success-msg" for="vat_no"> '+ vat_no + ' is valid. </label>' ).insertAfter( "#vat_no" );
								valid = true;
								$(".loaderDiv").hide();
								$('#vatFlag').val("1");
							}

							return valid;



						}else{
							valid = false;
							$.validator.messages.vat_noCheck = '';
									$("#vat_no-error").remove();
									$('#vat_no-success').remove();
									var url = BASE_URL+'index-vat.php';
									var vat_no = val;
									$.ajax({
										url: url,
										type: 'ajax',
										method: 'POST',
										dataType: 'json',
										data: {vat_no:vat_no},
										async: false,
										success:function(response){
											var address_id = $('#address_id').val();
											if (response.flag == 0) {

												if(response.validitiy == 'Valid'){
													$('#consulation_no').val(response.Identifier);
													$('#res_company_name').val(response.Company_name);
													$('#res_company_address').val(response.Company_address);

													addingVatLog(val,response,1,address_id);

													$.validator.messages.vat_noCheck = '';
													$( '<label id="vat_no-success" class="success-msg" for="vat_no"> '+ vat_no + ' is valid. </label>' ).insertAfter( "#vat_no" );
													valid = true;
													$(".loaderDiv").hide();
													$('#vatFlag').val("1");
												}else{

													addingVatLog(val,response,2,address_id);

													$.validator.messages.vat_noCheck = val+" is not valid.";
													valid = false;
													$(".loaderDiv").hide();
												}

											}else{

												addingVatLog(val,response,0,address_id);
												//$.validator.messages.vat_noCheck= 'Vat number checking is not available at the moment. Please try again later.';
												$.validator.messages.vat_noCheck= response.msg+". Vat will be charged.";
												valid = false;
												$(".loaderDiv").hide();
											}




										}

									});


									return valid;

						}


					// $(".loaderDiv").hide();
				}


			});

				return valid;

		}


	},  $.validator.messages.vat_noCheck);


	$('#address-form').on('submit',function(e){
		e.preventDefault();
		if($(this).valid()) {
			var flag = $("#flag").val();
			var fd = new FormData($('#address-form')[0]);
			fd.append('flag',flag);
            $.ajax({
                url: $(this).attr('action'),
                type: 'ajax',
                method: $(this).attr('method'),
                dataType: 'json',
                data: fd,
				processData: false,
				contentType: false,
                success: function(response) {
                    console.log(response);
					$('#ajax-spinner').hide();
                    if (response.flag == 1) {
                    	$("#WebShopCommonModal").modal('hide');
                        swal({
							title: "",
							icon: "success",
							text: response.msg,
							buttons: false,
							timer: 3000
						})

                        setTimeout(function() {
                            location.reload();
                        }, 1000);

                    } else {
                    	$("#WebShopCommonModal").modal('hide');
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

                        // setTimeout(function() {
                            // //window.location.href = response.redirect;

                        // }, 1000);
                    }
                }
            });
		}
	});
});

function addingVatLog(request,response,response_type,address_id=''){

	var url = BASE_URL+'CheckoutController/addVatLogging';
	$.ajax({
		url: url,
		type: 'ajax',
		method: 'POST',
		dataType: 'json',
		data: {request:request,response:response,response_type:response_type,address_id:address_id,type:1},
		success: function(response) {
			//console.log(response);

		}
	});

}
