$("#coming_products_table").dataTable().fnDestroy();

  table = $('#coming_products_table').DataTable({ 
    
    	"scrollCollapse": true,
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
		"bLengthChange" : true, //thought this line could hide the LengthMenu
		"bInfo":false,    
		"stateSave" : true,
		"recordsFiltered ":false,  
        "order": [], //Initial no order.
        "iDisplayLength": 20,
		"pageLength": 20,
		"searchDelay": 2000,
		"paging": true,
        "lengthMenu": [[10, 20, 30, 40, 50, -1], [10, 20, 30, 40, 50, "All"]],
        "ajax": {
          "url": BASE_URL+"ProductsNotifyController/loadordersajax",
          "type": "POST",
          "data": function ( ) { }	

        },
		"search": {
				"caseInsensitive": false
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
				"targets": [],
                "orderable": false
         }],
		"initComplete": function() {
				 
		}
			

    });


