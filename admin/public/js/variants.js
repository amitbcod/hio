$("#variantForm").validate({
    rules: {
		attribute_name: {required: true,},
		attribute_code: {required: true },
	},
    submitHandler: function(form) {
        var formData = new FormData($('#variantForm')[0]);
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
                if (response == "insert") {
                    $('#variantbtn').attr('disabled', 'disabled');
					swal('Success','Variant Insert successfully!','success');
						window.setTimeout( function() {
						window.location.href="/admin/variants";
						}, 1500);
                }else if(response == "update"){
                    $('#variantbtn').attr('disabled', 'disabled');
					swal('Success','Variant Update successfully!','success');
						window.setTimeout( function() {
						window.location.href="/admin/variants";
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