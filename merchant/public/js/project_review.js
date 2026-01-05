
$(document).ready(function(){

	$('#DataTables_Table_Project_Review_list').DataTable({
		'processing': true,
		'serverSide': true,
		'serverMethod': 'post',
		'language': {
			search: "" ,
			searchPlaceholder: "Search",
			paginate:{
				previous: '<i class="fas fa-angle-left"></i>',
				next: '<i class="fas fa-angle-right"></i>'
			}
		},
		'iDisplayLength': 100,
		'pageLength': 100,
		'lengthMenu': [[10, 25, 50, 100, 200, 500], [10, 25, 50, 100, 200, 500]],
		'ajax': {
			'url':BASE_URL+'ProductReviewController/ajaxreviewlist',
		},
		'columns': [
			{ data: 'id' },
			{ data: 'product_url' },
			{ data: 'customer_name' },
			{ data: 'email_id' },
			{ data: 'rating' },
			{ data: 'review' },
			{ data: 'created_at' },
			{ data: 'view_url' },
            { data: 'action' },
			{ data: 'delete' }

		]
	});

	$('#search_term').on('input', function(e) {
		e.preventDefault();
		getSearchProjectReviewList();

	});


    $("#ckbCheckAllSP").click(function () {
        $('input[name="chk_sp[]"]').prop('checked', $(this).prop('checked'));
    });

    $("#deleteall").click(function () {
        if($('input[type=checkbox]:checked').length == 0)
        {
        $('#deleteALLModal').modal('hide');
        swal({ title: "",text:'Please Select items to delete.' , icon: 'error' })
        return false;
        }
    });

});

function ViewReviewById(review_id){

	$.ajax({
        type: 'POST',
        dataType: 'json',
        url: BASE_URL+'ProductReviewController/ViewReviewById',
        data: {review_id:review_id},
        success:function(response){
			console.log(response);
			if(response.flag == 1){
				$("#pro-review-popup").html(response.data);
				$('#review_details').modal('show');
			}else{
				$('#review_details').modal('hide');
			}
		}
	});

}

function getSearchProjectReviewList(){
	var search = $.trim($('#search_term').val());
	var flag = "search";

	$.ajax({
		type: "POST",
		dataType: "html",
		url: BASE_URL+'ProductReviewController/getProductReviewList',
		data: {search:search, flag:flag},
		success: function(response) {
			$("#reviewListBlock").html(response);
			if ( $.fn.dataTable.isDataTable( '#DataTables_Table_Project_Review_list' ) ) {
				table = $('#DataTables_Table_Project_Review_list').DataTable();
			}
			else {
				table = $('#DataTables_Table_Project_Review_list').DataTable( {
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

				} );
			}
		}
	});
}

function DeleteReview(review_id){
    console.log(review_id);
    if(review_id!=''){
        $.ajax({
        type: 'POST',
        dataType: 'json',
        url: BASE_URL+'ProductReviewController/DeleteReview',
        data: {review_id:review_id},
        beforeSend: function () {
            $('#ajax-spinner').show();
        },
        success:function(response){
            console.log(response);
            $('#ajax-spinner').hide();
            if(response.flag==1){

            swal({
                title: "",
                icon: "success",
                text: response.msg,
                buttons: false,
                //timer:3000
            });

            setTimeout(function() {
                window.location.href = response.redirect;
            }, 1000);

            }else if(response.flag==2){

            swal({
                title: "",
                icon: "error",
                text: response.msg,
                buttons: false,
                //timer:3000
            });

            setTimeout(function() {
                window.location.href = response.redirect;
            }, 1000);

            }else{
            swal({
                title: "",
                icon: "error",
                text: response.msg,
                buttons: false,
                //timer:3000
            });

            setTimeout(function() {
                window.location.href = response.redirect;
            }, 1000);
            }
        }
        });
    }
}

function OpenBulkDeletePopup(e){
    e.preventDefault();
     var formData = new FormData($('#pr_listing_Form')[0]);
    $.ajax({
        type: "POST",
        dataType: "json",
        url: BASE_URL+"ProductReviewController/delete_all_product_reviews",
        data: formData,
        processData: false,
        contentType: false,
        async:false,
        complete: function () {
           $('#ajax-spinner').hide();
        },
        beforeSend: function(){
           $('#ajax-spinner').show();
        },
        success: function(response) {
            if (response.flag == 1) {
                swal({ title: "",text: response.msg, button: false, icon: 'success' },
                function() {location.reload(); })
            } else {
                $("#deleteALLModal").modal('hide');
                swal({ title: "",text: response.msg, button: false, icon: 'error' })
                return false;
            }
        }
    });
}

function change_reviews_status(action,id)
{
    if(action == 0)
    {
        var status = 0;
    }
    else if(action == 1)
    {
        var status = 1;
    }
                $.ajax({
                    type:"POST",
                    url:'ProductReviewController/change_reviews_status',
                    dataType:"json",
                    data :{status:status,id:id},
                    beforeSend:function()
                    {

                    },
                    success:function(response){
                        if( response.flag == "1")
                        {
                            swal({
                                title: "",
                                icon: "success",
                                text: response.msg,
                                buttons: false,
                            })

                              setTimeout(function() {
                              location.reload();

                            }, 1000);
                            location.reload();
                        }

                        else
                        {
                            swal({
                                title: "",
                                icon: "error",
                                text: response.msg,
                                buttons: false,
                            })
                        }
                    }
                });

}
