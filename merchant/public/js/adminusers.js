$(function() {
    $("#user-add").validate({
        rules: {
            first_name: 'required',
            last_name: 'required',
            password: 'required',
            mobile:'required',
            usertype: 'required',
         },
         messages: {
         },
         submitHandler: function(form) {
            var formData = new FormData($("#user-add")[0]);

            $.ajax({
                type: "POST",
                url: BASE_URL+"AdminuserController/add_admin_user_detail",
                dataType: 'json',
                data: formData,
                cache: false,
                processData: false,
                contentType: false,
                success: function (res) {
                   var dataResult = JSON.parse(JSON.stringify(res));
                   if (dataResult.status == 200) {
                    swal({
                        title: "",
                        icon: "success",
                        text: res.msg,
                        buttons: false,
                    },
                    function(){window.location = dataResult.redirect })
                    }
                    else{
                        alert('An Error Occurred! Try again Later');
                    }
                }
            });
         }
    });
});

function delete_user(id)
  {
    var confirmation = confirm("are you sure you want to delete the user?");

    if(confirmation) {
        $.ajax({
            type:'post',
            dataType:'json',
            url: BASE_URL+"AdminuserController/delete_users",
            data: {id:id},
            beforeSend: function() {

            },
            success: function(response){
                if(response.status == 200) {
                    swal({
                        title: "",
                        icon: "error",
                        text: response.msg,
                        buttons: false,
                    },
                    function(){ $('#adminUserLists').DataTable().ajax.reload(); })
                }
            }
        });
    }
  }
