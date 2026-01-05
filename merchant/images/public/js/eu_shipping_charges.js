$(document).ready(function(){

ShippingChargesDataTable();

  $('.delete-btn').click(function(){
    if( confirm('Are you sure you want to continue?')) {
        var id = $('#shipping_charge_id_hidden').val();
        $.ajax({
            type:"POST",
            url:BASE_URL+"EuShippingChargesController/delete_shipping_charge",
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

   
});

  
   function ShippingChargesDataTable()
{
    $("#shipping_charges_table").dataTable().fnDestroy();
    table = $('#shipping_charges_table').DataTable(
        {
          "order": [[ 0, "desc" ]],
          "info" : false,
          "lengthMenu": [[5,10, 20, 50, -1], [5, 10, 20, 50, "All"]],
          "language": {                
          "paginate": {
                        next: '<i class="fas fa-angle-right"></i>',
                        previous: '<i class="fas fa-angle-left"></i>'  
                       },
          "search": ""

         },
          'columnDefs': [{
                            "targets": [2,3,4],
                            "orderable": false
                         }],
        "ajax": {
          "url": BASE_URL+"EuShippingChargesController/load_eu_specialpricingajax",
          "type": "POST",
          "data":  {}
        },
        } );
    
    //  if ($('#shipping_charges_table tr').length < 10) 
    // {
    //         $('.dataTables_paginate').hide();
    // }
}
   

$('#eu_shipping_charges_form').on('submit',function(e){
  e.preventDefault();
if($(this).valid()) {
      var formData = new FormData($("#eu_shipping_charges_form")[0]);
        $.ajax({
          type:"POST",
          url:$(this).attr('action'),
          dataType:"json",
          data:formData,
          processData: false,
          contentType: false,
          
          success:function(response){
            // console.log(response);
            if( response.msg == "Success")
            {
              swal({
              title: "",
              icon: "success",
              text: response.msg,
              buttons: false,           
              },
              function() {
                    window.location = response.redirect;
                })
            }
            else
            {
              swal({
                title: "",
                icon: "error",
                text: response.msg,
                buttons: false,           
              },
              function(){location.reload(); })
            }
          }
        });
  }
});

$('#eu_shipping_charges_update_form').on('submit',function(e){
  e.preventDefault();
        if($(this).valid()) {
        var formData = new FormData($("#eu_shipping_charges_update_form")[0]);
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

// $(document).on( "click", '.trash',function(e) {
//         var id = $(this).attr("data-id");
//         $("#row_id").val(id);
//         //console.log(id);
//     });

  $("#deleteModalForRowForm").validate({
        ignore: ':hidden',      
        //ignore: ".ignore",
        submitHandler: function(form) {
            var formData = new FormData($('#deleteModalForRowForm')[0]);
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


  function deleteShippingCharge(id){
        if( confirm('Are you sure you want to continue?')) {
            $.ajax({
                type:"POST",
                url:BASE_URL+"EuShippingChargesController/delete_shipping_charge",
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

    function editShippingCharge(id){
          var shipping_charge_id = id ; //get the attribute value
          // console.log(shipping_charge_id);
          $.ajax({
              url :BASE_URL+"EuShippingChargesController/get_shipping_charge_data",
              data:{shipping_charge_id : shipping_charge_id},
              method:'POST',
              dataType:'json',
              success:function(result) {

                    var final = result.response;
                    // console.log(final);
                    $('#shipping-update').modal({backdrop: 'static', keyboard: true, show: true});

                    $('#ship_method_name_update').val(final.ship_method_name); //hold the response in id and show on popup

                    // $('#vat_percentage_update').val(final.vat_percentage);
                    if(final.status == 1){
                        $("#status_update").prop('checked', true);
                    }else{
                        $("#status_update").prop('checked', false);
                    }
                    // $('#country_code_update').val(final.country_code);
                    $('#shipping_charge_id_hidden').val(final.id);

            }

          });
       

    }

    function OpenDownloadPopup()
    {
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"EuShippingChargesController/OpenDownloadPopup",
            data: {},
            async:false,
            complete: function () { 
            },  
            beforeSend: function(){
                 $('#ajax-spinner').show();
            },          
            success: function(response) {
                 $('#ajax-spinner').hide();
                if(response!='error'){
                
                    $("#FBCUserCommonModal").modal();
                    $("#modal-content").html(response);
                }else{
                    
                }
                
            }
        });
    }

    function DownloadSampleCSV()
    {   
            window.location.href=BASE_URL+'EuShippingChargesController/DownloadSampleCSV';
            $("#FBCUserCommonModal").modal('hide');
    }

    function OpenUploadPopup()
    {
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"EuShippingChargesController/OpenUploadPopup",
            data: {},
            async:false,
            complete: function () { 
            },  
            beforeSend: function(){
                 $('#ajax-spinner').show();
            },          
            success: function(response) {
                 $('#ajax-spinner').hide();
                if(response!='error'){
                
                    $("#FBCUserCommonModal").modal();
                    $("#modal-content").html(response);
                }else{
                    
                }
                
            }
        });
    }


$(document).on("change", "#upload_csv_file", function(){
     //Do something
      $('#check_data').removeClass('d-none');
       $('#bulk_upload').addClass('d-none');
     $('#check_data').removeAttr('disabled');
});
    
    function CheckCSVShippingData()
    {
        var upload_csv_file=$('#upload_csv_file').val();
        if(upload_csv_file==''){
            swal('Error','Please upload file','error');
            return false;
        }else{
            
            
             var fd = new FormData();
            fd.append('upload_csv_file', $('#upload_csv_file')[0].files[0]); // since this is your file input

            $.ajax({
                url: BASE_URL+"EuShippingChargesController/CheckCSVShippingData",
                type: "post",
                dataType: 'html',
                 contentType:false,
                  cache:false,
                  processData:false,
                data: fd,
                success: function(response) {
                    
                    var obj = JSON.parse(response);
                    if(obj.status == 200) {
                        
                        swal("Success", obj.message, "success");
                        $('#bulk_upload').removeClass('d-none');
                        $('#check_data').addClass('d-none');
                        
                        
                    } else {
                    
                        swal("Error", obj.message, "error");
                        $('#bulk_upload').addClass('d-none');
                        $('#check_data').removeClass('d-none');
                        $('#csv_error').html(response.message);
                        return false;
                        
                    }
                    
                },
                error: function() {
                    swal('Error','Something went wrong','error');
                    return false;
                }
            });
            
            
        }
    }

    function UpdateShippingData()
    {
        var upload_csv_file=$('#upload_csv_file').val();
        if(upload_csv_file==''){
            swal('Error','Please upload file','error');
            return false;
        }else{
            
            
             var fd = new FormData();
            fd.append('upload_csv_file', $('#upload_csv_file')[0].files[0]); // since this is your file input

            $.ajax({
                url: BASE_URL+"EuShippingChargesController/UpdateShippingData",
                type: "post",
                dataType: 'html',
                 contentType:false,
                  cache:false,
                  processData:false,
                data: fd,
                success: function(response) {
                    //console.log(response);return false;
                    
                    var obj = JSON.parse(response);
                    if(obj.status == 200) {
                        
                        $('#FBCUserCommonModal').modal('hide');
                        swal("Success", obj.message, "success");
                    
                    } else {
                    
                        swal("Error", obj.message, "error");
                        return false;
                        
                    }
                    
                },
                error: function() {
                    swal('Error','Something went wrong','error');
                    return false;
                }
            });
            
            
        }
    }