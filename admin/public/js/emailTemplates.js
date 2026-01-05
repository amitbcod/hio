
 function openTemplates(id,code)
{
    if(id!='' && code!=''){
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"EmailTemplateController/openTemplates",
            data: {id:id,code:code},
        //  async:false,
            complete: function () { 
            },  
            beforeSend: function(){
                // $('#ajax-spinner').show();
            },          
            success: function(response) {
                $("#FBCUserCommonModal").modal();
                 $("#modal-content").html(response);
            }
        });
    }else{
        return false;
    }   
}

function SaveTemplates()
{
    var hidden_temp_id=$('#hidden_temp_id').val();
    var code=$('#code').val();
    var template_subject_trans=$('#template_subject_trans').val();
    var template_content_trans = CKEDITOR.instances['template_content_trans'].getData();
    
    if(template_subject_trans==''){
        swal("Error", "Please enter templates subject ", "error");             
        return false;       
    }
   else if(template_content_trans ===""){
        swal("Error", "Please enter templates content", "error");             
        return false;       
    }
    else{
            $.ajax({
            type: "POST",
            dataType: "json",
            url: BASE_URL+"EmailTemplateController/emailTemplates",
            data: {template_subject_trans:template_subject_trans,code:code,hidden_temp_id:hidden_temp_id,template_content_trans:template_content_trans},
            success: function(response) {
                if(response.flag == 1)
                    {
                        console.log(response)
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
                        setTimeout(function() {
                        location.reload();

                        }, 1000);
                    }             
            }
        });
    }
}