
     $('input[id="chk_sidebar[]"]').change(function(){
        if (!$(this).prop("checked")){
           $(this).closest('label').parent().find(':checkbox').prop('checked', false); 
        }else{
         
            $(this).closest('li').parent().parent().find(':checkbox').first(':checkbox').prop('checked', $(this).prop('checked')); 
            $(this).closest('li').parent().parent().closest('ul').parent().find(':checkbox').first(':checkbox').prop('checked', $(this).prop('checked'));      
        }
    });

$("#hidediv").hide();
$( document ).ready(function() {
    var id = $("#resource_access").val();
    if(id == 1)
    {
      $("#hidediv").show();

	        
    }else{
      $("#hidediv").hide();
    }
});

function changeValue() {
    var id = $("#resource_access").val();
    if(id == 1)
    {
      $("#hidediv").show();

	        
    }else{
      $("#hidediv").hide();
    }
}


$('#roleSubmit').click(function(event) {
	event.preventDefault();
	var myArray = [];
    $(":checkbox:checked").each(function() {
        myArray.push(this.value);
    });
    if(myArray == '')
    {
      swal({ title: "",text: 'Select Atleast One Checkbox...!', button: false, icon: 'error' });
      return false;

    }
    else
    {
      var role_name = $('#role_name').val(); 
      var access = $('#resource_access').val();
      var roleId = $('#roleId').val();
      $.ajax({
        type : "POST",
        url  : BASE_URL+"DashboardController/CreateRoleResource",
        dataType : "JSON",
        data : {role_name :role_name,roleId:roleId,resource_access:access,myArray:myArray},
          success: function(response){
          if(response.flag == 1) {
                  swal({ title: "",text: response.msg, button: false, icon: 'success' })
                  setTimeout(function() { window.location.href = response.redirect }, 1000);
              } else {
                  swal({ title: "",text: response.msg, button: false, icon: 'error' });
                  return false;
              } 
       }

     });
    }	
});



    function deleteRole(id)
    {
     var cnf = confirm("Are you sure you want to delete this data?");
     if (cnf == true) {
        $.ajax({
          type: "POST",
          url: BASE_URL+"DashboardController/employee_delete_role",
          dataType: "JSON",
          data: {id:id},
          success: function(response) {
           if(response.flag == 1) {
	            swal({ title: "",text: response.msg, button: false, icon: 'success' })
	            setTimeout(function() {
                        location.reload();      }, 1000);
	        } else {
	        	swal({ title: "",text: response.msg, button: false, icon: 'error' });
	            return false;
	        }
          }
        });
      }
    }


  