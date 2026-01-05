var save_method; //for save method string
	var table;
	var a = $(window).height(); // screen height  
	var b = 250;  var pageHeight =a-b;        
	if(pageHeight<200){          
	  pageHeight=400;        
	}
			

$(document).ready(function(){

	FilterMessageDataTable();
	FilterClosedMessageDataTable(); 
	console.log("In close");

	$('#messageAdminForm').validate({
		ignore: [],
		debug: false,
	 rules: {
            message_to: {
                required: true,
                //email: true,
            },
            message_subject: {
                required: true,
            },
			message_description:{
				 required: function(element) 
				{
				  return CKEDITOR.instances.message_description.updateElement();
				}

                       
			}
			
        }
});
$('#messageAdminForm').on('submit',function(e){
	e.preventDefault();
if($(this).valid()) {
			var formData = new FormData($("#messageAdminForm")[0]);
				$.ajax({
					type:"POST",
					url:$(this).attr('action'),
					dataType:"json",
					data:formData,
					processData: false,
					contentType: false,
					beforeSend:function()
					{
						
					},
					success:function(response){
						if( response.msg == "Success")
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

$('#messageReplyAdminForm').validate({
		ignore: [],
		debug: false,
	 rules: {
            message_description:{
				 required: function(element) 
				{
				  return CKEDITOR.instances.message_description.updateElement();
				}

                       
			}
			
        }
});

$('#messageReplyAdminForm').on('submit',function(e){
	e.preventDefault();
if($(this).valid()) {
			var formData = new FormData($("#messageReplyAdminForm")[0]);
				$.ajax({
					type:"POST",
					url:$(this).attr('action'),
					dataType:"json",
					data:formData,
					processData: false,
					contentType: false,
					beforeSend:function()
					{
						//$("#add_submit").prop("disabled",true).css({"background":"#868686","color":"#fff"});
					},
					success:function(response){
						if( response.msg == "Success")
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
	$(".priority-list").change(function () {
			// alert("here");
			var success = confirm('Are you sure want to change the priority?');
			if (success == true) {
				var new_val = $(this).val();
				var msg_id = $(this).data('id');
				
				
				//alert(new_val+"----"+msg_id)
				$.ajax({
					url: BASE_URL+"change-priority",
					method: 'POST',
					type: 'ajax',
					dataType: 'json',
					data:  { msg_id : msg_id, priority_val : new_val }, 
					success: function(response) {
						
						if (response.flag == 1) {
							swal({ text: response.msg, button: false, icon: 'success' })
							if(response.flag == 1){
								setTimeout(function() { window.location.href = BASE_URL+'message'; }, 1000);
							}
						}else{
							swal({ text: response.msg, button: false, icon: 'error' })
							return false;
						}
					},
					error: function (response) {
						console.log(response.responseText);
						return false;
					}
				});
			
			}else{
				location.reload();

			}
		});
		
		$("#closed-message-topic").click(function () {
			
			var success = confirm('Are you sure want to close the topic?');
			if (success == true) {
				var id = $(this).data('id');
				
				$.ajax({
					url: BASE_URL+"close-message-topic",
					method: 'POST',
					type: 'ajax',
					dataType: 'json',
					data:  { id : id}, 
					success: function(response) {
						
						if (response.flag == 1) {
							swal({ text: response.msg, button: false, icon: 'success' })
							if(response.flag == 1){
								setTimeout(function() { window.location.href = BASE_URL+'message'; }, 1000);
							}
						}else{
							swal({ text: response.msg, button: false, icon: 'error' })
							return false;
						}
					},
					error: function (response) {
						console.log(response.responseText);
						return false;
					}
				});
				
			}	
			
		
		});	
	


});

function change_priority_listing(event,id){
	
	//alert("there");
	var success = confirm('Are you sure want to change the priority?');
		if (success == true) {
			var new_val = event;			
			var msg_id = id;
			
			//alert( event.value +"==="+$(this).val() );
			$.ajax({
				url: BASE_URL+"change-priority",
				method: 'POST',
				type: 'ajax',
				dataType: 'json',
				data:  { msg_id : msg_id, priority_val : new_val }, 
				success: function(response) {
					
					if (response.flag == 1) {
						swal({ text: response.msg, button: false, icon: 'success' })
						if(response.flag == 1){
							setTimeout(function() { window.location.href = BASE_URL+'message'; }, 1000);
						}
					}else{
						swal({ text: response.msg, button: false, icon: 'error' })
						return false;
					}
				},
				error: function (response) {
					console.log(response.responseText);
					return false;
				}
			});
		
		}else{
			location.reload();

		}
}

function FilterMessageDataTable(){

	
  $("#datatableMessage").dataTable().fnDestroy();
  //datatables
  $('#datatableMessage').DataTable({ 
    "scrollY":        pageHeight,
    "scrollCollapse": true,
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "scrollX":true,
		"bInfo" : false,
		"stateSave" : true,
        "order": [], //Initial no order.
        "iDisplayLength": 25,
		"pageLength": 25,
		"searchDelay": 2000,
        "lengthMenu": [[10, 25, 50, 100, 200, 500, -1], [10, 25, 50, 100, 200, 500, "All"]],
        // Load data for the table's content from an Ajax source
        "ajax": {
          "url": BASE_URL+"MessageController/loadmessagesajax",
          "type": "POST",
        },
		"search": {
				"caseInsensitive": false
		},
		"fnDrawCallback": function(oSettings) {
				
					if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {
						
						$(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
						$(oSettings.nTableWrapper).find('.dataTables_length').hide();
						
					}else{
						$(oSettings.nTableWrapper).find('.dataTables_paginate').show();
						$(oSettings.nTableWrapper).find('.dataTables_length').show();
					}
					
			  },
		"language": {                
			"infoFiltered": "",
			"search": '',
			"emptyTable":     "No data available in table",
			"searchPlaceholder": "Search",
			"paginate": {
			  next: '<i class="fas fa-angle-right"></i>',
			  previous: '<i class="fas fa-angle-left"></i>'  
			}

		},
		"initComplete": function() {
				 
		}
			

      });
		 $("#datatableMessage").on( 'init.dt', function () {
   $("table td .unread").closest("tr").addClass("unread");
} );
	  

}


function FilterClosedMessageDataTable(){

	$("#datatableMessageClosed").dataTable().fnDestroy();
	//datatables
	$('#datatableMessageClosed').DataTable({ 
	  "scrollY":        pageHeight,
	  "scrollCollapse": true,
		  "processing": true, //Feature control the processing indicator.
		  "serverSide": true, //Feature control DataTables' server-side processing mode.
		  "scrollX":true,
		  "bInfo" : false,
		  "stateSave" : true,
		  "order": [], //Initial no order.
		  "iDisplayLength": 25,
		  "pageLength": 25,
		  "searchDelay": 2000,
		  "lengthMenu": [[10, 25, 50, 100, 200, 500, -1], [10, 25, 50, 100, 200, 500, "All"]],
		  // Load data for the table's content from an Ajax source
		  "ajax": {
			"url": BASE_URL+"MessageController/loadClosedmessagesajax",
			"type": "POST",
		  },
		  "search": {
				  "caseInsensitive": false
		  },
		  "fnDrawCallback": function(oSettings) {
				  
					  if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {
						  
						  $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
						  $(oSettings.nTableWrapper).find('.dataTables_length').hide();
						  
					  }else{
						  $(oSettings.nTableWrapper).find('.dataTables_paginate').show();
						  $(oSettings.nTableWrapper).find('.dataTables_length').show();
					  }
					  
				},
		  "language": {                
			  "infoFiltered": "",
			  "search": '',
			  "searchPlaceholder": "Search",
			  "paginate": {
				next: '<i class="fas fa-angle-right"></i>',
				previous: '<i class="fas fa-angle-left"></i>'  
			  }
  
		  },
		  "initComplete": function() {
				   
		  }
			  
  
		});
		
		 $("#datatableMessageClosed").on( 'init.dt', function () {
   $("table td .unread").closest("tr").addClass("unread");
} );
  
  }
  
  



