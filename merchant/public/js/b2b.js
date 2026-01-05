$(document).ready(function() {
	console.log('ready');
    $(function() {
	  	$("#B2BStatusChk").on("click",function() {
	    	$(".toggle-b2b-details").toggle(this.checked);
	  	});
		
		$("#dropShipChk").on("click",function() {
	    	$(".toggle-dropshp").toggle(this.checked);
	  	});
		
		$("#buyInChk").on("click",function() {
	    	$(".toggle-buy-in").toggle(this.checked);
	  	});
		
		$("#priceChk").on("click",function() {
	    	$(".toggle-price").toggle(this.checked);
	  	});
	});
	
	$('#b2bCatList').DataTable( {
		language:{  
			paginate:{
				previous: '<i class="fas fa-angle-left"></i>',
				next: '<i class="fas fa-angle-right"></i>'
			}
		},
		paging: false,
		searching: false,
		info: false,
		lengthChange: false 
	} );
	
	
	$("#B2BAccessForm").validate({
        ignore: ':hidden',		
        //ignore: ".ignore",
        rules: {
            // dropShipChk: {
                // required: function() {
					// return $('[name="B2BStatusChk"]:checked').val() == 'on'; 
				// },
            // },
            // buyInChk: {
                // required: function() {
					// return $('[name="B2BStatusChk"]:checked').val() == 'on'; 
				// },
            // },
            dropshipTime: {
				digits: true,
                required: function() {
					return $('[name="dropShipChk"]:checked').val() == 'on'; 
				},
            },
			buyinTime: {
				digits: true,
                required: function() {
					return $('[name="buyInChk"]:checked').val() == 'on'; 
				},
            },
			incPriceChk: {
                require_from_group: [1, '.price-chk']
            },
			decPriceChk: {
                require_from_group: [1, '.price-chk']
            },
        },
		messages: {
			incPriceChk:{require_from_group: "Please select atleast one option"},
			decPriceChk:{require_from_group: ""},
		},
		beforeSend: function(){
			$('#ajax-spinner').show();
		},
        submitHandler: function(form) {
			
			var formData = new FormData($('#B2BAccessForm')[0]);
			
            $.ajax({
                url: form.action,
                type: 'ajax',
                method: form.method,
                dataType: 'json',
                data: formData,
				processData: false,
				contentType: false,
                success: function(response) {
                    console.log(response);
					$('#ajax-spinner').hide();
                    if (response.flag == 1) {
                        swal({ title: "",text: response.msg, button: false, icon: 'success' })
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
	                    return false;
                    }
                }
            });
        }
    });
});

function readFileURL(input) {
	if (input.files && input.files[0]) {
		$("#"+input.id).parent('.custom-file').hide();
		var file = input.files[0];
		var extension = file.name.split('.').pop().toLowerCase();
		// console.log("Show type of image: ", file.type.split("/")[1]);
		//if(file.type == 'application/pdf')
		console.log(file);
		console.log(extension);
		
	if ( /\.(jpe?g|png|pdf|doc|docx)$/i.test(file.name) ) {
			
			var reader = new FileReader();
			var pdfImage = BASE_URL+'public/images/pdf-icon.png';
			var docImage = BASE_URL+'public/images/document-icon.png';
			reader.onload = function(e) {
				if(extension == 'pdf'){
					srcImage = pdfImage;
				}else if(extension == 'doc' || extension == 'docx'){
					srcImage = docImage;
				}else{
					srcImage = e.target.result;
				}
				
				$('#upload_' + input.id).html("<span class=\"single-img\">" + "<a href=\"javascript:void(0);\" onclick=\"removeFile()\" class=\"rm-media\">X</a><img class=\"thumb\" src=\"" + srcImage + "\" title=\"" + file.name + "\"/>" + "</span>");
				
				//alert('#upload_' + input.id + " .remove")
				 $('#upload_' + input.id + " .rm-media").click(function(){
					//alert(input.id);
					//alert(111111111)
					$(this).parent(".single-img").remove();
					$("#" + input.id).val('');
					$("#" + input.id).parent('.custom-file').show();
				  });
				
			}

			reader.readAsDataURL(input.files[0]);
		}else{
			$("#"+input.id).parent('.custom-file').show();
			swal({ title: "",text: file.type, button: false, icon: 'error' })
            return false;
		}
	}
}

function removeFile(inputId){
	// alert(inputId);
	// alert(2222222222222)
	$('#upload_' + inputId).empty();
	$("#" + inputId).val('');
	$("#hidden_" + inputId).val('');
	$("#" + inputId).parent('.custom-file').show();
}

function B2BCheckRelatedCat(elem,category_id,level){
	if($(elem).is(':checked')){
		if(level==0){
			if($('.b2b-pc-'+category_id).length>0){
				$('.b2b-pc-'+category_id).prop('checked',true);
				$('.b2b-pc-'+category_id).prop('readonly',true);
				
			}
		}else {
			
			if($('.b2b-c-'+category_id).length>0){
				$('.b2b-c--'+category_id).prop('checked',false);
				$('.b2b-c-'+category_id).prop('readonly',false);
				
			}
		}
	}else{
		
		if(level==0){
			if($('.b2b-pc-'+category_id).length>0){
				$('.b2b-pc-'+category_id).prop('checked',false);
				$('.b2b-pc-'+category_id).prop('readonly',false);
				
			}
		}else {
			
			if($('.b2b-c-'+category_id).length>0){
				$('.b2b-c--'+category_id).prop('checked',false);
				$('.b2b-c-'+category_id).prop('readonly',false);
				
			}
		}
		
	}
}