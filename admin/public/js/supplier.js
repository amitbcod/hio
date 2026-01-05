console.log('loaded.........');
$(document).ready(function() {
	if ( $.fn.dataTable.isDataTable( '#supplierList' ) ) {
		table = $('#supplierList').DataTable();
	}
	else {
		table = $('#supplierList').DataTable( {
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
			stateSave: true,
			//order: [[ 6, "desc" ]]
		} );
	}

	$('#supplierCatList').DataTable( {
		language:{
			paginate:{
				previous: '<i class="fas fa-angle-left"></i>',
				next: '<i class="fas fa-angle-right"></i>'
			}
		},
		paging: false,
		searching: false,
		info: false,
		lengthChange: false
	} );

	$('#savedOrdersList').DataTable( {
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
		// order: [[ 5, "desc" ]]
		order: []
	} );

	$('#savedOrdersListApplied').DataTable( {
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
		// order: [[ 6, "ASC" ]]
		order: []
	} );

	$('#search_term').on('input', function(e) {
		e.preventDefault();
		getSearchSupplierList();

	});


	$("#catListForm").validate({
        ignore: ':hidden',
        rules: {
            "checked_cat[]": {
			},
        },

		beforeSend: function(){
			$('#ajax-spinner').show();
		},
        submitHandler: function(form) {
			var formData = new FormData($('#catListForm')[0]);

			$('#ajax-spinner').show();

            $.ajax({
                url: form.action,
                type: 'ajax',
                method: form.method,
                dataType: 'json',
                data: formData,
				processData: false,
				contentType: false,
				cache: false,
                success: function(response) {
					$('#ajax-spinner').hide();

                    if (response.flag == 1) {
                        $('#search-tab').html(response.msg);
                        
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
	                    return false;
                    }
                }
            });
        }
    });

});

function getSearchSupplierList(){
	var search = $.trim($('#search_term').val());
	var flag = "search";

	$.ajax({
		type: "POST",
		dataType: "html",
		url: BASE_URL+"SupplierController/getSeacrhList/",
		data: {search:search, flag:flag},
		success: function(response) {
			$("#supplierListBlock").html(response);
			if ( $.fn.dataTable.isDataTable( '#supplierList' ) ) {
				table = $('#supplierList').DataTable();
			}
			else {
				table = $('#supplierList').DataTable( {
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

function viewDocument(type, file_name){
	if (type != '' && file_name != '') {
		$('#FBCUserSecondaryModal').modal();
		response = '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" id="iframe" src="" allowfullscreen="true"></iframe></div><div class="download-discard-small"><button class="white-btn" data-dismiss="modal">Close</button></div>';
		$("#modal-content-second").html(response);
		if (type == 1) {
			src = S3_URL + "documents/payment_terms/" + file_name;
		}

		if (type == 2) {
			src = S3_URL + "documents/terms_condition/" + file_name;
		}

		$('#iframe').attr("src", src);
	}
}

function getSavedOrders(){

	$.ajax({
		type: "POST",
		dataType: "html",
		url: BASE_URL+"SupplierController/getSavedOrders/",
		success: function(response) {
			//console.log(response);
			$("#saved-tab").html(response);
			if ( $.fn.dataTable.isDataTable( '#savedOrdersList' ) ) {
				table = $('#savedOrdersList').DataTable();
			}
			else {
				table = $('#savedOrdersList').DataTable( {
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


function SupplierCheckRelatedCat(elem,category_id,level){
	if($(elem).is(':checked')){
		if(level==0){
			if($('.b2b-pc-'+category_id).length>0){
				$('.b2b-pc-'+category_id).prop('checked',true);
				$('.b2b-pc-'+category_id).prop('disabled',true);

			}
		}else {

			if($('.b2b-c-'+category_id).length>0){
				$('.b2b-c--'+category_id).prop('checked',false);
				$('.b2b-c-'+category_id).prop('disabled',false);

			}
		}
	}else{

		if(level==0){
			if($('.b2b-pc-'+category_id).length>0){
				$('.b2b-pc-'+category_id).prop('checked',false);
				$('.b2b-pc-'+category_id).prop('disabled',false);

			}
		}else {

			if($('.b2b-c-'+category_id).length>0){
				$('.b2b-c--'+category_id).prop('checked',false);
				$('.b2b-c-'+category_id).prop('disabled',false);

			}
		}

	}
}
