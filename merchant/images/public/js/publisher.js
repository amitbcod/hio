$("#publisherForm").validate({
    submitHandler: function(form) {
        var formData = new FormData($('#publisherForm')[0]);
        $.ajax({
            url: form.action,
            type: 'ajax',
            method: form.method,
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.flag == 1) {
                    swal({ title: "",text: response.msg, button: false, icon: 'success' })
                    setTimeout(function() { window.location.href = response.url;}, 1500);
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

$(document).ready(function(){
      
    $( "#passwordCheck" ).click(function() {
        var disabled = $("#password").prop('disabled');
        if (disabled) {
            $("#password").prop('disabled', false);        // if disabled, enable
            $("#password").val('');
        }
        else {
            $("#password").prop('disabled', true);        // if enabled, disable
            $("#password").val('password');
        }
  });
});
function OpenPasswordChangePopup() {
	$.ajax({
		type: "POST",
		dataType: "html",
		url: BASE_URL+"PublisherController/openAdminUserPasswordPopup",
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

function ConfirmPublisherDelete(id){

	if(id!=''){
		swal({
							title: "Are you sure? ",
							text: "",
							type: "warning",
							showCancelButton: true,
							confirmButtonColor: "#3085d6",
							 cancelButtonColor: '#d33',
							confirmButtonText: "Yes, delete it!",
							cancelButtonText: "Cancel",
							closeOnConfirm: false,
							closeOnCancel: false
						}, function(isConfirm) {
							if (isConfirm) {
								DeleteCategory(id);
							} else {
								swal.close();
							}
						});
	}
}

function DeleteCategory(id){
	if(id!=''){
			$.ajax({
				type: "POST",
				dataType: "html",
				url: BASE_URL+"PublisherController/deletePublisher/",
				data: {id:id},				
				beforeSend: function () { 
					$('#ajax-spinner').show();
				},			
				success: function(response) {
					$('#ajax-spinner').hide();
					if(response=='success'){
                        
                        swal('Success','Publisher deleted successfully!','success');
                  window.setTimeout( function() {
                  window.location.reload();
                  }, 1500);
					}else{
						return false;
					}
				}
			});
	}else{
		return false;
	}
}