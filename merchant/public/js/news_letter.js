$( document ).ready(function() {
    console.log( "ready!" );
    
    $('#editnewslettertexteditform').find('textarea').each(function(index) { 
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

});

$("#editnewslettertexteditform").validate({
        ignore: ':hidden',      
        //ignore: ".ignore",
        
        /*messages: {
            "customFil[]": "This field is required.",
        },*/
        submitHandler: function(form) {
            var formData = new FormData($('#editnewslettertexteditform')[0]);
            
            $.ajax({
                url: form.action,
                type: 'ajax',
                method: form.method,
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() { 
                      $("#save_newsletter_edit").prop('disabled', true); // disable button
                    },
                success:function(response) {
                     console.log(response);
                     $("#save_newsletter_edit").prop('disabled', false); // enable button
                    if (response.flag == 1) {
                        swal({ title: "",text: response.msg, button: false, icon: 'success' },
                        function() {location.reload(); })
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
                        return false;
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
        }
    });

