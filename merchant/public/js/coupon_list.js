
$(document).ready(function(){  
	
    if ( $.fn.dataTable.isDataTable( '#DataTables_Discount_list' ) ) {
		table = $('#DataTables_Discount_list').DataTable();
	}
	else {
		table = $('#DataTables_Discount_list').DataTable({
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
				iDisplayLength: 100,
				pageLength: 100,
				// "searchDelay": 2000,
				lengthMenu: [[25, 50, 100, 200, 500, -1], [ 25,50, 100, 200, 500, "All"]],
				});
	}
	
	$('#search_discount_term').on('input', function(e) {
		e.preventDefault();
        // alert(222222);
        getSearchDiscountList();
	});

});  

function getSearchDiscountList(){
	var search = $.trim($('#search_discount_term').val());
	var flag = "search";
	console.log(search);
	$.ajax({
		type: "POST",
		dataType: "html",
		url: BASE_URL+'ReportController/getdiscountlist',
		data: {search:search, flag:flag},
		success: function(response) {
            console.log(response);
			$("#reviewDiscountListBlock").html(response);
			if ( $.fn.dataTable.isDataTable( '#DataTables_Discount_list' ) ) {
				table = $('#DataTables_Discount_list').DataTable();
			} else {
				table = $('#DataTables_Discount_list').DataTable( {
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
					// ordering : true,
					// order: [[ 0, "desc" ]],
                    iDisplayLength: 100,
                    pageLength: 100,
                    // "searchDelay": 2000,
					// columnDefs : [{"targets":0, "type":"date-eu"}],
                    lengthMenu: [[25, 50, 100, 200, 500, -1], [ 25,50, 100, 200, 500, "All"]],
				} );
			}
		}
	});
}
