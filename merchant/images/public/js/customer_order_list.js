console.log('loaded.........');
$(document).ready(function() {
	if ( $.fn.dataTable.isDataTable( '#DataTables_Table_Customer_list' ) ) {
		table = $('#DataTables_Table_Customer_list').DataTable({
				"order": [[ 1, "desc" ]]
		});
	}
	else {
		table = $('#DataTables_Table_Customer_list').DataTable( {
			language:{  
				paginate:{
					previous: '<i class="fas fa-angle-left"></i>',
					next: '<i class="fas fa-angle-right"></i>'
				}
			},
			paging: true,
			pageLength: 100,
			searching: false,
			info: false,
			lengthChange: false,
			stateSave: true,	
			"order": [[ 1, "desc" ]]
		} );
	}

	
	
	$('#search_txt').on('input paste search', function(e) {
	var str = $("#search_txt").val().length;
	//console.log(str);
	if(str > 2 )
	{
		e.preventDefault();
			getWebShopCustomerList();
	}
		
	});
	$('#search_txt').on('search', function(e) {
		e.preventDefault();
			
			getWebShopCustomerList();
	});

	

	
});

function getWebShopCustomerList(){
//console.log('loaded function');
	var search = $.trim($('#search_txt').val());
	var flag = "search";
	$.ajax({
		type: "POST",
		dataType: "html",
		url: BASE_URL+"CustomerController/getwebshopCustomerList/",
		data: {search:search, flag:flag},
		success: function(response) {
			//console.log(response);
			//$("#search-tab").html(response);
			$("#DataTables_Table_Customer_list").html(response);
			if ( $.fn.dataTable.isDataTable( '#DataTables_Table_Customer_list' ) ) {
				table = $('#DataTables_Table_Customer_list').DataTable();
			}
			else {
				table = $('#DataTables_Table_Customer_list').DataTable( {
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