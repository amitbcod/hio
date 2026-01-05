$(document).ready(function() {
	
    table = $('#vat_setting_list').DataTable({
        "pageLength" : 20,
        "lengthMenu" : [[5, 10, 20, -1], [5, 10, 20, 'All']],
        "searching": true,
        "info" : false,
        "lengthChange" : true,
        "order": [[ 0, "desc" ]],
        "language":{  
                "paginate":{
                    "previous": '<i class="fas fa-angle-left"></i>',
                    "next": '<i class="fas fa-angle-right"></i>'
                },
                "search": "",
                "searchPlaceholder": "Search"
            }
    });

    $("#vat-create").on('hide.bs.modal', function(){
        $('#vat_create_form')[0].reset();

        var validator = $( "#vat_create_form" ).validate();
        validator.resetForm();
    });

    
    $('#vat_create_form').on('submit',function(e){
	e.preventDefault();
        if($(this).valid()) {
        var formData = new FormData($("#vat_create_form")[0]);
            $.ajax({
                type:"POST",
                url:$(this).attr('action'),
                dataType:"json",
                data:formData,
                processData: false,
                contentType: false,
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
        }
    });
});


    $('#currency_update_form').on('submit',function(e){
	e.preventDefault();
        if($(this).valid()) {
        var formData = new FormData($("#currency_update_form")[0]);
            $.ajax({
                type:"POST",
                url:$(this).attr('action'),
                dataType:"json",
                data:formData,
                processData: false,
                contentType: false,
                
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
        }
    });



$('.delete-btn').click(function(){

    if( confirm('Are you sure you want to continue?')) {

        var id = $('#currency_id_hidden').val();

        $.ajax({
            type:"POST",
            url:BASE_URL+"Multi_Currencies_Controller/delete_currency",
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


}); 

function updateCurr(id){

        $.ajax({
              url :BASE_URL+"Multi_Currencies_Controller/get_currency_data",
              data:{id : id},
              method:'POST',
              dataType:'json',
              success:function(result) {
                    var final = result.response;

                  
                    $('#currency_update').modal({backdrop: 'static', keyboard: true, show: true});

                    $('#currency_name_update').val(final.name); //hold the response in id and show on popup
                    $('#currency_code_update').val(final.code);
                    $('#currency_conversion_rate_update').val(final.conversion_rate);
                    $('#currency_symbol_update').val(final.symbol);


                   
                    if(final.is_default_currency == 1){
                        $("#is_default_currency_update").prop('checked', true);

                    }else{
                        $("#is_default_currency_update").prop('checked', false);
                    }

                    if(final.status == 1){
                        $("#status_update").prop('checked', true);

                    }else{
                        $("#status_update").prop('checked', false);
                    }
                    
                    $('#currency_id_hidden').val(final.id);

                    if (final.id==1) {
                        $('.delete-btn').hide();
                    }else{
                        $('.delete-btn').show();
                    }
            }
          });
    }     


function deleteCurrency(id){

        if( confirm('Are you sure you want to continue?')) {

            $.ajax({
                type:"POST",
                url:BASE_URL+"Multi_Currencies_Controller/delete_currency",
                dataType:"json",
                data:{id:id},
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