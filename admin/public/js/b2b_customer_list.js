console.log('loaded.........');
$(document).ready(function() {
	if ( $.fn.dataTable.isDataTable( '#DataTables_Table_B2BCustomerList' ) ) {
		table = $('#DataTables_Table_B2BCustomerList').DataTable();
	}
	else {
		table = $('#DataTables_Table_B2BCustomerList').DataTable( {
			language:{  
				paginate:{
					previous: '<i class="fas fa-angle-left"></i>',
					next: '<i class="fas fa-angle-right"></i>'
				}
			},
			paging: true,
			searching: false,
			info: false,
			lengthChange: false,
			stateSave: true	
		} );
	}
	
	
	$('#search_txt').on('input', function(e) {
	var str = $("#search_txt").val().length;
	if(str > 2 )
	{
		e.preventDefault();
		getB2BCustomerList();

	}
	});

	
});

function getB2BCustomerList(){
 	var search = $.trim($('#search_txt').val());
	var flag = "search";
	$.ajax({
		type: "POST",
		dataType: "html",
		url: BASE_URL+"B2BController/getB2BCustomerList/",
		data: {search:search, flag:flag},
		success: function(response) {
			$("#DataTables_Table_B2BCustomerList").html(response);
			if ( $.fn.dataTable.isDataTable( '#DataTables_Table_B2BCustomerList' ) ) {
				table = $('#DataTables_Table_B2BCustomerList').DataTable();
			}
			else {
				table = $('#DataTables_Table_B2BCustomerList').DataTable( {
					language:{  
						paginate:{
							previous: '<i class="fas fa-angle-left"></i>',
							next: '<i class="fas fa-angle-right"></i>'	
						}
					},
					paging: true,
					searching: false,
					info: false,
					lengthChange: false,
					stateSave: true	
				} );
			}
		}
	});
}