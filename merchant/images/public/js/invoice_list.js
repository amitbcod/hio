console.log('loaded.........');
$(document).ready(function() {
	if ( $.fn.dataTable.isDataTable( '#DataTables_invoice_list' ) ) {
		table = $('#DataTables_invoice_list').DataTable({
				"order": [[ 0, "desc" ], [ 1, "desc"  ]],
				"pageLength" : 5,
    			"lengthMenu" : [[5, 10, 20, -1], [5, 10, 20, 'All']],
    			"searching": true,
				"info" : false,
				"lengthChange" : true,
				"language":{  
						"paginate":{
							"previous": '<i class="fas fa-angle-left"></i>',
							"next": '<i class="fas fa-angle-right"></i>'
						},
						"search": "",
						"searchPlaceholder": "Search"
					}
		});
	}
	else {
		table = $('#DataTables_invoice_list').DataTable( {
			"language":{  
						"paginate":{
							"previous": '<i class="fas fa-angle-left"></i>',
							"next": '<i class="fas fa-angle-right"></i>'
						},
						"search": "",
						"searchPlaceholder": "Search"
					},
			//language: { search: "", searchPlaceholder: "Search..." },
			paging: true,
			searching: true,
			info: false,
			lengthChange: true,
			stateSave: true,	
			"order": [[ 0, "desc" ], [ 1, "desc" ]],
			"pageLength" : 5,
    		"lengthMenu" : [[5, 10, 20, -1], [5, 10, 20, 'All']]
		} );
	}
});

/*start b2b*/
$(document).ready(function() {
	if ( $.fn.dataTable.isDataTable( '#DataTables_b2b_invoice_list' ) ) {
		table = $('#DataTables_b2b_invoice_list').DataTable({
				"order": [[ 0, "desc" ], [ 1, "desc"  ]],
				"pageLength" : 5,
    			"lengthMenu" : [[5, 10, 20, -1], [5, 10, 20, 'All']],
    			"searching": true,
				"info" : false,
				"lengthChange" : true,
				"language":{  
						"paginate":{
							"previous": '<i class="fas fa-angle-left"></i>',
							"next": '<i class="fas fa-angle-right"></i>'
						},
						"search": "",
						"searchPlaceholder": "Search"
					}
		});
	}
	else {
		table = $('#DataTables_b2b_invoice_list').DataTable( {
			"language":{  
						"paginate":{
							"previous": '<i class="fas fa-angle-left"></i>',
							"next": '<i class="fas fa-angle-right"></i>'
						},
						"search": "",
						"searchPlaceholder": "Search"
					},
			//language: { search: "", searchPlaceholder: "Search..." },
			paging: true,
			searching: true,
			info: false,
			lengthChange: true,
			stateSave: true,	
			"order": [[ 0, "desc" ], [ 1, "desc" ]],
			"pageLength" : 5,
    		"lengthMenu" : [[5, 10, 20, -1], [5, 10, 20, 'All']]
		} );
	}
});
/*end b2b*/

