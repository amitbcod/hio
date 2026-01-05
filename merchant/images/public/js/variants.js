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