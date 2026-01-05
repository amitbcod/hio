
	var save_method; //for save method string
            var table;
            var a = $(window).height(); // screen height  
            var b = 250;  var pageHeight =a-b;        
            if(pageHeight<200){          
              pageHeight=400;        
            }
			
		$(document).ready(function() {	
			RequestedReturnsODT();
			
			
		});


function RequestedReturnsODT(){

	
  $("#DataTables_Table_WebshopReturnOrdersR").dataTable().fnDestroy();
  //datatables
  table = $('#DataTables_Table_WebshopReturnOrdersR').DataTable({ 
    
    "scrollCollapse": true,
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        //"scrollX":true,
		"bLengthChange" : false, //thought this line could hide the LengthMenu
		"bInfo":false,    
		"stateSave" : true,
        "order": [], //Initial no order.
        "iDisplayLength": 25,
		"pageLength": 25,
		"searchDelay": 2000,
        "lengthMenu": [[10, 25, 50, 100, 200, 500, -1], [10, 25, 50, 100, 200, 500, "All"]],
        // Load data for the table's content from an Ajax source
        "ajax": {
          "url": BASE_URL+"ReturnOrderController/requestedreturndorder",
          "type": "POST",
          "data": function ( d ) {
			
			
          }
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
		'columnDefs': [{
                "targets": [5,6,7],
                "orderable": false
         }],
		"initComplete": function() {
				 
		}
			

      });
}

