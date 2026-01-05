var save_method; //for save method string
var table;
var a = $(window).height(); // screen height  
var b = 250;  var pageHeight =a-b;        
if(pageHeight<200){          
	pageHeight=400;        
}

$(document).ready(function() {	

 $("#DT_B2BOrderItems").dataTable().fnDestroy();
  //datatables
  	table = $('#DT_B2BOrderItems').DataTable({ 
		"scrollCollapse": true,
		"processing": true, //Feature control the processing indicator.
		"searching": false,
		"bInfo" : false,
		"stateSave" : true,
		"order": [], //Initial no order.
		"iDisplayLength": 25,
		"pageLength": 25,
		"lengthMenu": [[10, 25, 50, 100, 200, 500, -1], [10, 25, 50, 100, 200, 500, "All"]],
		
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
			"targets": [0,5],
			"orderable": false
         }],
		"initComplete": function() {				 
		}		
	});
});