console.log('loaded.........');
$(document).ready(function() {
	if ( $.fn.dataTable.isDataTable( '#DataTables_Table_Customer_list' ) ) {
		table = $('#DataTables_Table_Customer_list').DataTable({
				"order": [[ 0, "desc" ], [ 1, "desc"  ]],
				"pageLength" : 20,
    			"lengthMenu" : [[5, 10, 20, -1], [5, 10, 20, 'All']],
    			"searching": true,
				"info" : false,
				"lengthChange" : true,
				"language":{  
						"paginate":{
							previous: '<i class="fas fa-angle-left"></i>',
							next: '<i class="fas fa-angle-right"></i>'
						},
						"search": "",
						"searchPlaceholder": "Search"
					}
		});
	}
	else {
		table = $('#DataTables_Table_Customer_list').DataTable( {
			"serverSide": true,
			"processing": true,
			"scrollCollapse": true,
			"language":{  
						"paginate":{
							previous: '<i class="fas fa-angle-left"></i>',
							next: '<i class="fas fa-angle-right"></i>'
						},
						"search": "",
						"searchPlaceholder": "Search"
					},
			language: { search: "", searchPlaceholder: "Search..." },
			paging: true,
			searching: true,
			info: false,
			blengthChange: true,
			stateSave: true,	
			"order": [],
			"pageLength" : 20,
			"iDisplayLength": 20,
    		"lengthMenu" : [[5, 10, 20, -1], [5, 10, 20, 'All']],
			"ajax": {
				"url": BASE_URL+"CustomerController/loadcustomerssajax",
				"type": "POST",
				 "data": { },
			  },
		} );
	}
});

