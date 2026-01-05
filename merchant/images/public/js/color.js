function deleteColor(id){

    if( confirm('Are you sure you want to continue?')) {

        $.ajax({
            type:"POST",
            url:BASE_URL+"BaseColorManagementController/delete_Color",
            dataType:"json",
            data:{id:id},
            //processData: false,
            //contentType: false,
            success:function(response){
                if( response.flag == 1)
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

        
    }else{
        return false;
    }
}


$(document).ready(function(){
    $("#color_detail_form").validate({
        ignore: ':hidden:not("#variant")',		
        rules: {
            title: {
                required: true,
                remote: {
                    url: BASE_URL+"BaseColorManagementController/checkColorName",
                    type: "POST",
                    data: {
                    action:$('#action').val(),
                    id:$('#id').val(),
                    title: function() {
                        return $( "#title" ).val();
                    }
                    }
                }
            },
            "variant[]" :{
                minlength: 1,
                required: true,
               
            },

        },
        messages: {
            title: {
                remote: "Base color already in use."
            }
            
        },
        

        submitHandler: function(form) {
            var formData = new FormData($("#color_detail_form")[0]);
            $.ajax({
                type:"POST",
                url:BASE_URL+"BaseColorManagementController/submit_color_management_details",
                dataType:"json",
                data:formData,
                processData: false,
                contentType: false,
            
                success:function(response){
                    console.log(response);
                    if( response.flag == 1)
                    {
                        console.log(response.redirect);
                        swal({
                            title: "",
                            icon: "success",
                            text: response.msg,
                            buttons: false,	
                                            
                        })
                        
                        setTimeout(function() {
                            window.location.href = response.redirect;
                            // location.reload();
                        }, 1000);
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
});

