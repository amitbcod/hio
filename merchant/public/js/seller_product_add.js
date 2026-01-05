$(document).ready(function() {

	$("#product-frm-add").validate({

		ignore: [],
        debug: false,
        rules: {
            product_name: {
                required: true,
            },
			product_code: {
                required: true,
                remote: {
                    url: BASE_URL+"sellerproduct/checkproductcode",
                    type: "POST",
					data: {
					  flag:'add',
					  product_code: function() {
						return $( "#product_code" ).val();
					  }
					}
                 }
            },

            description: {
                required: function()
                        {
                         CKEDITOR.instances.description.updateElement();
					},
				ckrequired:true
            },
			highlights: {
                required: function()
                        {
                         CKEDITOR.instances.highlights.updateElement();
				},
				ckrequired:true
            },

			'gallery_image[]': {
                required: true,
            },
			product_type: {
                required: true
            },
			product_publication: {
				required: true,
			},
			sku: {
                required: function(element) {
					if ( $('#product_type').val() == 'simple') {
					  return true;
					}else{
					  return false;
					}
				},
				validatesku: function(element) {
					if ( $('#product_type').val() == 'simple') {
					  return true;
					}else{
					  return false;
					}
				},
				remote: {
                    url: BASE_URL+"sellerproduct/checkskuexist",
                    type: "POST",
					data: {
					  flag:'add',
					  product_type:$('#product_type').val(),
					  sku: function() {
						return $( "#sku" ).val();
					  }
					}
                 }

            },
			price: {
                required: function(element) {
					if ( $('#product_type').val() == 'simple') {
					  return true;
					}else{
					  return false;
					}
				}
            },
			cost_price: {
                required: function(element) {
					if ( $('#product_type').val() == 'simple') {
					  return true;
					}else{
					  return false;
					}
				}
            },
			stock_qty: {
                required: function(element) {
					if ( $('#product_type').val() == 'simple') {
					  return true;
					}else{
					  return false;
					}
				}
            },
			barcode:{
				/*
                required: function(element) {
					if ( $('#product_type').val() == 'simple') {
					  return false;
					}else{
					  return false;
					}
				},*/
				remote: {
                    url: BASE_URL+"sellerproduct/checkbarcodeexist",
                    type: "POST",
					data: {
					  flag:'add',
					  product_type:$('#product_type').val(),
					  barcode: function() {
						return $( "#barcode" ).val();
					  }
					}
                 }
            },
        },
		groups: {
            GeneratedSkuGroup: "variant_sku[]"
        },
		 errorPlacement: function(error, element) {

			if ($(element).hasClass("wrong-sku")) {
				error.insertAfter($(element).closest("table"));
			} else {
				error.insertAfter(element);
			}
		},
        messages: {
			product_code: {
				//required: "Please enter valid product code.",
				remote: "Product code already in use."
			},
			sku: {
				//required: "Please enter valid sku.",
				remote: "Sku already in use."
			},
			barcode: {
				//required: "Please enter valid sku.",
				remote: "Barcode already in use."
			}
          //  inputPassword: { "minlength": "Please enter 8 or more characters." },
        },
        submitHandler: function(form) {
			// $('#save_product').attr('disabled',true);

		var form_obj = $('#product-frm-add')[0];
		var totalfiles = document.getElementById('gallery_image').files.length;
        var formData = new FormData(form_obj);

		// Read selected files
		/*
		for (var index = 0; index < totalfiles; index++) {
		  formData.append("gallery_image[]", document.getElementById('gallery_image').files[index]);
		}
		*/

		$.each($("input[type='file']")[0].files, function(i, file) {
			formData.append('gallery_image[]', file);
		});
		$.each($(".image"), function (i, obj) {
			$.each(obj.files, function (j, file) {
				formData.append('product_image_'+i+'[]', file);
			});
		});
            $.ajax({
                url: form.action,
                type: 'ajax',
                method: form.method,
                dataType: 'json',
				enctype: 'multipart/form-data',
				processData: false,  // Important!
				contentType: false,
				cache: false,
                data: formData,
				beforeSend: function(){
					// $('#ajax-spinner').show();
				},
                success: function(response) {
					// $('#save_product').attr('disabled',false);
				//	console.log(response);return false;
					$('#ajax-spinner').hide();
                    if (response.status == 200) {
						swal("Success", response.message, "success");
                        setTimeout(function() {
								window.location.href=BASE_URL+"seller/warehouse";
                        }, 1000);

                    } else {
						swal("Error", response.message, "error");
                        return false;
                    }

                }
            });
        }
    });


	$('.number-input').each(function() {
        $(this).rules("add",
            {
                required: true,
				number:true,
                messages: {
                    required: "Field is required",
                }
            });
    });

	$('.required-field').each(function() {
        $(this).rules("add",
            {
                required: true,
                messages: {
                    required: "Field is required",
                }
            });
    });


	jQuery.validator.addMethod('ckrequired', function (value, element, params) { var idname = jQuery(element).attr('id'); var messageLength = jQuery.trim ( CKEDITOR.instances[idname].getData() ); return !params || messageLength.length !== 0; }, "This field is required");

	$.validator.addMethod(
			"regex_pcname",
			function(value, element, regexp) {
				var re = new RegExp(regexp);
				return this.optional(element) || re.test(value);
			},
			"Only allowed numbers(0-9), letters(a-z A-Z), underscore(_) and hyphen(-)."
	);

	$("#product_code").rules("add", { regex_pcname: "^[a-zA-Z0-9-_]+$" });

	$.validator.addMethod("validatesku", function(value, element) {
		//test user value with the regex
		var regex_spe="^[a-zA-Z0-9-_:\/ ]+$";
			var re_new = new RegExp(regex_spe);
		return this.optional(element) || re_new.test(value);
	  }, "Letters, numbers, slash(/), dash(-), space and colon(:) only please");

	  $('.valid-sku').each(function() {
        $(this).rules("add",
            {
                validatesku: true

            });
    });
});

$(document).ready(function() {
	$('#product_publication').change(function(e) {
		e.preventDefault();

		var pubId = $("#product_publication").val();

		$.ajax({
			url: BASE_URL+"Sellerproduct/get_publisher_data",
			method:'POST',
			dataType: 'JSON',
			data:{
				'id' : pubId
			},
			success : function(data) {
				$('#pub_com_percentage').val(data.data);
			},
			error: function(data) {

			}
		});
	});

	$('input[type=radio][name=type-of-commission]').change(function() {
		if (this.value == 0) {
			$("#pub_com_percentage").attr("readonly", true);

			var pubId = $("#product_publication").val();

			$.ajax({
				url: BASE_URL+"Sellerproduct/get_publisher_data",
				method:'POST',
				dataType: 'JSON',
				data:{
					'id' : pubId
				},
				success : function(data) {
					$('#pub_com_percentage').val(data.data);
				},
				error: function(data) {

				}
			});
		}
		if (this.value == 1) {
			$("#pub_com_percentage").attr("readonly", false);

			var pubId = $("#product_publication").val();

			$.ajax({
				url: BASE_URL+"Sellerproduct/get_product_commission_data",
				method:'POST',
				dataType: 'JSON',
				data:{
					'id' : pubId
				},
				success : function(data) {
					$('#pub_com_percentage').val(data.data);
				},
				error: function(data) {

				}
			});
		}
	});
});

$(document).ready(function () {
	$("#product_publication").on("change", function () {
		$.ajax({
			url: BASE_URL + "sellerproduct/addCommissionInputField",
			method: "POST",
			success: function (data) {
				$("div#commission_inputs").html(data);

				var pubId = $("#product_publication").val();

				var check = $(
					'input[type=radio][name="type-of-commission"]:checked'
				).val();

				if (check == 0) {
					$.ajax({
						url: BASE_URL + "Sellerproduct/get_publisher_data",
						method: "POST",
						dataType: "JSON",
						data: {
							id: pubId,
						},
						success: function (resp) {
							$("#pub_com_percentage").val(resp.data);
						},
					});
				} else {
					$("#pub_com_percentage").val("");
				}
			},
		});
	});
});

$(document).on("change", "input[type=radio][name=type-of-commission]", function () {
	var check = $('input[type=radio][name="type-of-commission"]:checked').val();
	if (check == 0) {
		$("#pub_com_percentage").attr("readonly", true);

		var pubId = $("#product_publication").val();

		$.ajax({
			url: BASE_URL+"Sellerproduct/get_publisher_data",
			method:'POST',
			dataType: 'JSON',
			data:{
				'id' : pubId
			},
			success : function(data) {
				$('#pub_com_percentage').val(data.data);
			},
			error: function(data) {

			}
		});
	}
	if (this.value == 1) {
		$("#pub_com_percentage").attr("readonly", false);

		var pubId = $("#product_publication").val();

		$.ajax({
			url: BASE_URL+"Sellerproduct/get_product_commission_data",
			method:'POST',
			dataType: 'JSON',
			data:{
				'id' : pubId
			},
			success : function(resp) {
				$('#pub_com_percentage').attr("placeholder", "0.00");
				$('#pub_com_percentage').val('');
			},
			error: function(resp) {

			}
		});
	}
});