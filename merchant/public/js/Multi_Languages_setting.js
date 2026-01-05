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


    $('#language_update_form').on('submit',function(e){
	e.preventDefault();
        if($(this).valid()) {
        var formData = new FormData($("#language_update_form")[0]);
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

        var id = $('#language_id_hidden').val();

        $.ajax({
            type:"POST",
            url:BASE_URL+"Multi_Languages_Controller/delete_language",
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

function updateLang(id){

         $.ajax({
              url :BASE_URL+"Multi_Languages_Controller/get_language_data",
              data:{id : id},
              method:'POST',
              dataType:'json',
              success:function(result) {
                    var final = result.response;

                  console.log(result);
                    $('#language_update').modal({backdrop: 'static', keyboard: true, show: true});
                    $('#name_update').val(final.name);
                    $('#display_name_update').val(final.display_name); //hold the response in id and show on popup
                    $('#code_update').val(final.code);
            
                   
                    if(final.is_default_language == 1){
                        $("#is_default_language_update").prop('checked', true);

                    }else{
                        $("#is_default_languagey_update").prop('checked', false);
                    }

                    if(final.status == 1){
                        $("#status_update").prop('checked', true);

                    }else{
                        $("#status_update").prop('checked', false);
                    }

                    if(final.is_communication_language == 1){
                        $("#is_communication_language_upd").prop('checked', true);

                    }else{
                        $("#is_communication_language_upd").prop('checked', false);
                    }
                    
                    $('#language_id_hidden').val(final.id);

                    if (final.id==1) {
                        $('.delete-btn').hide();
                    }else{
                        $('.delete-btn').show();
                    }
            }
          });
    }   


function deleteLanguage(id){

        if( confirm('Are you sure you want to continue?')) {

            $.ajax({
                type:"POST",
                url:BASE_URL+"Multi_Languages_Controller/delete_language",
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

    function OpenEditCategory(id,code)
{
    console.log(id);
    if(id!='' && code!=''){
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"Multi_Languages_Controller/openeditcategorypopup",
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

function SaveExistingCategory()
{
    var cat_description=$('#cat_description').val();
    var cat_name=$('#cat_name').val();
    console.log(cat_name);
    var hidden_cat_id=$('#hidden_cat_id').val();
    var code=$('#code').val();

    if(cat_name==''){
        swal("Error", "Please enter category name", "error");             
        return false;       
    }
    else{  
            $.ajax({
            type: "POST",
            dataType: "json",
            url: BASE_URL+"Multi_Languages_Controller/updaterootcategory",
            data: {code:code,hidden_cat_id:hidden_cat_id,cat_name:cat_name,cat_description:cat_description},
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
}


function OpenEditVariants(id,code){
    
    if(id!='' && code!=''){
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"Multi_Languages_Controller/OpenEditVariantsPopup",
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

function OpenEditAttributes(id,code){
    
    if(id!='' && code!=''){
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"Multi_Languages_Controller/OpenEditAttributesPopup",
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

function saveAttributesTranslation(){

    var attributes_desc=$('#attributes_desc').val();
    var attributes_name=$('#attributes_name').val();
    var id=$('#id').val();
    var attr_type=$('#attr_type').val();
    var code=$('#code').val();

    if(attributes_name==''){
        swal("Error", "Please enter attribute name", "error");             
        return false;       
    }
    else{  
            $.ajax({
            type: "POST",
            dataType: "json",
            url: BASE_URL+"Multi_Languages_Controller/saveAttributesTranslation",
            data: {code:code,id:id,attributes_name:attributes_name,attributes_desc:attributes_desc,attr_type:attr_type},
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
}


function saveVariantTranslation(){

    var variant_desc=$('#variant_desc').val();
    var variant_name=$('#variant_name').val();
    var id=$('#id').val();
    var attr_type=$('#attr_type').val();
    var code=$('#code').val();

    if(variant_name==''){
        swal("Error", "Please enter variant name", "error");             
        return false;       
    }
    else{  
            $.ajax({
            type: "POST",
            dataType: "json",
            url: BASE_URL+"Multi_Languages_Controller/saveVariantTranslation",
            data: {code:code,id:id,variant_name:variant_name,variant_desc:variant_desc,attr_type:attr_type},
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
}


