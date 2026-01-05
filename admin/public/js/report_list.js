
$(document).ready(function(){  

    if ( $.fn.dataTable.isDataTable( '#DataTables_Report_list' ) ) {
		table = $('#DataTables_Report_list').DataTable();
	}
	else {
		table = $('#DataTables_Report_list').DataTable( {
			language:{  
				paginate:{
					previous: '<i class="fas fa-angle-left"></i>',
					next: '<i class="fas fa-angle-right"></i>'
				}
			},
			paging: true,
			searching: false,
			info: false,
			lengthChange: true,
			stateSave: true,
			iDisplayLength: 25,
			pageLength: 25,
			// "searchDelay": 2000,
			lengthMenu: [[25, 50, 100, 200, 500, -1], [ 25,50, 100, 200, 500, "All"]],
		} );
	}
	
	$('#search_term').on('input', function(e) {
		e.preventDefault();
		getSearchReportList();
	
	});
    
});  

function getSearchReportList(){
	var search = $.trim($('#search_term').val());
	var flag = "search";
	
	$.ajax({
		type: "POST",
		dataType: "html",
		url: BASE_URL+'ReportController/getreportlist',
		data: {search:search, flag:flag},
		success: function(response) {
			$("#reviewListBlock").html(response);
			if ( $.fn.dataTable.isDataTable( '#DataTables_Report_list' ) ) {
				table = $('#DataTables_Report_list').DataTable();
			}
			else {
				table = $('#DataTables_Report_list').DataTable( {
					language:{  
						paginate:{
							previous: '<i class="fas fa-angle-left"></i>',
							next: '<i class="fas fa-angle-right"></i>'
						}
					},
					paging: true,
					searching: false,
					info: false,
					lengthChange: true,
					stateSave: true,
					iDisplayLength: 25,
					pageLength: 25,
                    lengthMenu: [[25, 50, 100, 200, 500, -1], [ 25,50, 100, 200, 500, "All"]],                   
				} );
			}
		}
	});
}
