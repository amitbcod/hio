$("#categoryForm").validate({
	    submitHandler: function(form) {
            var formData = new FormData($('#categoryForm')[0]);
	        $.ajax({
                url: form.action,
                type: 'ajax',
                method: form.method,
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
				beforeSend: function(){
					$('#ajax-spinner').show();
			   },
	            success: function(response) {
					window.setTimeout( function() {
						$('#ajax-spinner').hide();
					},1500);					
	                if (response == "success") {
						$('#categorybtn').attr('disabled', 'disabled');
						swal('Success','Category Add successfully!','success');
						window.setTimeout( function() {
						window.location.href="/admin/category";
						}, 1500);
					}else if(response == "update"){
						$('#categorybtn').attr('disabled', 'disabled');
						swal('Success','Category Update successfully!','success');
						window.setTimeout( function() {
						window.location.href="/admin/category";
						}, 1500);
					}else{
	                    swal({ title: "",text: response.msg, button: false, icon: 'success' })
	                    return false;
	                }
	            },
	            error: function (response) {
	                console.log(response.responseText);
	                return false;
	            }
	        });
	        return false;
	    }
	});


	



