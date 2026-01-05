$( document ).ready(function() {

    //console.log( "ready!" );
    $('#editcontactustextform').find('textarea').each(function(index) { 
        var id=$(this).attr('id');
        
           var editorInstance = CKEDITOR.replace(id,
                    {
                        on:
                       {
                           'instanceReady': function(evt) {
                               evt.editor.document.on('keyup', function() {
                                   document.getElementById(id).value = evt.editor.getData();
                                    
                               });

                              evt.editor.document.on('paste', function() {
                                  document.getElementById(id).value = evt.editor.getData();
                               });
                           }
                       }
                    });
            });

    $(function() {
        $("#enalble_email").on("change",function() {
            $(".email_sec").toggle(this.checked);
                 var temp = $('#email');
                   // coup.toggle();
                    if (temp.prop('required')) {
                        temp.prop('required', false);
                    } else {
                        temp.prop('required', true);
                    }
                 });
        $("#enable_phone").on("change",function() {
            $(".phone_sec").toggle(this.checked);
                 var temp1 = $('#phone');
                   // coup.toggle();
                    if (temp1.prop('required')) {
                        temp1.prop('required', false);
                    } else {
                        temp1.prop('required', true);
                    }
                 });
        $("#enable_address").on("change",function() {
            $(".add_sec").toggle(this.checked);
                 var temp2 = $('#main_office');
                   // coup.toggle();
                    if (temp2.prop('required')) {
                        temp2.prop('required', false);
                    } else {
                        temp2.prop('required', true);
                    }
                 });
        
    });

    $('#editcontactustextform').on('submit',function(e){
		e.preventDefault();

	    for (instance in CKEDITOR.instances) 
        {
            CKEDITOR.instances[instance].updateElement();
        }
         var formData = new FormData($("#editcontactustextform")[0]);

            $.ajax({
            type:"POST",
            url:BASE_URL+'WebshopController/submit_contactus_text',
            dataType:"json",
            data:formData,
            processData: false,
            contentType: false,
            beforeSend:function()
            {
                $("#save_contact_edit").prop('disabled', true); // disable button
            },
            success:function(response){
                console.log(response);
                $("#save_contact_edit").prop('disabled', false); // enable button
                if( response.flag == 1)
                {
                    swal({
                        title: "",
                        icon: "success",
                        text: response.msg,
                        //buttons: false,						
                    },function(){ window.location = response.redirect;   }
                    )
                }
                
                else
                {
                    swal({
                        title: "",
                        icon: "error",
                        text: response.msg,
                        //buttons: false,						
                    },
                    function(){location.reload(); })
                }
            },
            error:function(err,xhr){
                 console.log(err);
                 console.log(xhr);
                  },
            complete:function(){
                  console.log('done');
            }
        });
    });
		       
});




function editContactUs(id,code)
{
    if(id!='' && code!=''){
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"WebshopController/openEditLangTranslate",
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


function SaveContactUsTrans()
{
    var hidden_contact_id=$('#hidden_contact_id').val();
    var code=$('#code').val();
    var contact_us_message = CKEDITOR.instances['contact_us_message_trans'].getData();
    var contact_us_message_block2 = CKEDITOR.instances['contact_us_message_trans2'].getData();
    var contact_us_message_block3 = CKEDITOR.instances['contact_us_message_trans3'].getData();

            $.ajax({
            type: "POST",
            dataType: "json",
            url: BASE_URL+"WebshopController/SaveContactTranslate",
            data: {contact_us_message:contact_us_message,code:code,hidden_contact_id:hidden_contact_id,contact_us_message_block2:contact_us_message_block2,contact_us_message_block3:contact_us_message_block3},
            success: function(response) {
                console.log(response);
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

