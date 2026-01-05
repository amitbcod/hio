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


    $('#vat_update_form').on('submit',function(e){
	e.preventDefault();
        if($(this).valid()) {
        var formData = new FormData($("#vat_update_form")[0]);
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

        var vat_id = $('#vat_id_hidden').val();

        $.ajax({
            type:"POST",
            url:BASE_URL+"VatController/delete_vat",
            dataType:"json",
            data:{vat_id:vat_id},
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

function updateVat(vat_id){
 $.ajax({
              url :BASE_URL+"VatController/get_vat_data",
              data:{vat_id : vat_id},
              method:'POST',
              dataType:'json',
              success:function(result) {
                    var final = result.response;

                  
                    $('#vat-update').modal({backdrop: 'static', keyboard: true, show: true});
                    $('#deduct_vat_update').val(final.deduct_vat); //hold the response in id and show on popup
                    $('#vat_percentage_update').val(final.vat_percentage);
                   
                    if(final.is_eu_country == 1){
                        $("#is_eu_country_update").prop('checked', true);

                    }else{
                        $("#is_eu_country_update").prop('checked', false);
                    }
                    $('#country_code_update').val(final.country_code);
                    $('#vat_id_hidden').val(final.id);
            }
          });
}   


function deleteVat(vat_id){

        if( confirm('Are you sure you want to continue?')) {

            $.ajax({
                type:"POST",
                url:BASE_URL+"VatController/delete_vat",
                dataType:"json",
                data:{vat_id:vat_id},
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