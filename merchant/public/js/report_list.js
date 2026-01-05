
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
			// ordering : true,
			// order: [[ 0, "desc" ]],
            // bLengthChange : true, //thought this line could hide the LengthMenu
		// "bInfo" : false,
		// "stateSave" : true,
        // "order": [ 0, "desc" ], //Initial no order.
		// columnDefs : [{"targets":0, "type":"date-eu"}],
        iDisplayLength: 25,
		pageLength: 25,
		// "searchDelay": 2000,
       	lengthMenu: [[25, 50, 100, 200, 500, -1], [ 25,50, 100, 200, 500, "All"]],
		} );
	}
	
	$('#search_term').on('input', function(e) {
		//if(e.which == 13){//Enter key pressed
		e.preventDefault();
			//alert(222222);
			getSearchReportList();
		//}
	});

    
    // $("#ckbCheckAllSP").click(function () {
    //     $('input[name="chk_sp[]"]').prop('checked', $(this).prop('checked'));
    // });

    // $("#deleteall").click(function () {
    //     if($('input[type=checkbox]:checked').length == 0)
    //     {
    //     $('#deleteALLModal').modal('hide');
    //     swal({ title: "",text:'Please Select items to delete.' , icon: 'error' })
    //     return false;
    //     }
    // });

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
					// ordering : true,
					// order: [[ 0, "desc" ]],
                    iDisplayLength: 25,
					pageLength: 25,
                    // "searchDelay": 2000,
					// columnDefs : [{"targets":0, "type":"date-eu"}],
                    lengthMenu: [[25, 50, 100, 200, 500, -1], [ 25,50, 100, 200, 500, "All"]],
                    
				} );
			}
		}
	});
}

// function DeleteReview(review_id){
//     console.log(review_id);
//     if(review_id!=''){
//         $.ajax({
//         type: 'POST',
//         dataType: 'json',
//         url: BASE_URL+'ProductReviewController/DeleteReview', 
//         data: {review_id:review_id},
//         beforeSend: function () {
//             $('#ajax-spinner').show();						
//         },
//         success:function(response){
//             console.log(response);
//             $('#ajax-spinner').hide();
//             if(response.flag==1){
            
//             swal({
//                 title: "",
//                 icon: "success",
//                 text: response.msg,
//                 buttons: false,	
//                 //timer:3000
//             });
            
//             setTimeout(function() {
//                 window.location.href = response.redirect;
//             }, 1000);

//             }else if(response.flag==2){

//             swal({
//                 title: "",
//                 icon: "error",
//                 text: response.msg,
//                 buttons: false,	
//                 //timer:3000
//             });
            
//             setTimeout(function() {
//                 window.location.href = response.redirect;
//             }, 1000);

//             }else{
//             swal({
//                 title: "",
//                 icon: "error",
//                 text: response.msg,
//                 buttons: false,	
//                 //timer:3000
//             });
            
//             setTimeout(function() {
//                 window.location.href = response.redirect;
//             }, 1000);
//             }
//         }
//         });	
//     }
// }

// function OpenBulkDeletePopup(e){
//     e.preventDefault();
//      var formData = new FormData($('#pr_listing_Form')[0]);
//     $.ajax({
//         type: "POST",
//         dataType: "json",
//         url: BASE_URL+"ProductReviewController/delete_all_product_reviews",
//         data: formData,
//         processData: false,
//         contentType: false,
//         async:false,
//         complete: function () {
//            $('#ajax-spinner').hide();
//         },  
//         beforeSend: function(){
//            $('#ajax-spinner').show();
//         },      
//         success: function(response) {
//             if (response.flag == 1) {
//                 swal({ title: "",text: response.msg, button: false, icon: 'success' },
//                 function() {location.reload(); })
//             } else {
//                 $("#deleteALLModal").modal('hide');
//                 swal({ title: "",text: response.msg, button: false, icon: 'error' })
//                 return false;
//             }          
//         }
//     });
// }
  