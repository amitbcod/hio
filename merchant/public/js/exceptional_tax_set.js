 $(document).ready(function() {
    // console.log('hi');
});
 $("#exceptional_tax_set_form").validate({
        ignore: ':hidden',      
        //ignore: ".ignore",
        rules: {
            chk_cat_menu: {
                required: true,
                minlength: 1,
            }
        },
        submitHandler: function(form) {
            var formData = new FormData($('#exceptional_tax_set_form')[0]);
            console.log(formData);
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
                        swal({ title: "",text: response.msg, button: false, icon: 'success' },
                        function() {location.reload(); })
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
                        return false;
                    }
                }
            });
        }
    });