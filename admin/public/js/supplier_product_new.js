
$(document).ready(function() {


var allow_dropship=$('#allow_dropship').val();
var allow_buyin=$('#allow_buyin').val();

if(allow_dropship=='0' && allow_buyin=='0')
{
	$('.buyin-checkbox').attr('disabled',true);
	$('.virtual-checkbox').attr('disabled',true);
	$('.dropship-checkbox').attr('disabled',true);
	
}else if(allow_buyin=='1' && allow_dropship=='0'){
	$('.buyin-checkbox').attr('disabled',false);
	$('.virtual-checkbox').attr('disabled',false);
	$('.dropship-checkbox').attr('disabled',true);
	
}else if(allow_buyin=='0' && allow_dropship=='1'){
	$('.buyin-checkbox').attr('disabled',true);
	$('.virtual-checkbox').attr('disabled',false);	
	$('.dropship-checkbox').attr('disabled',false);
}






$("#productListForm").validate({
	ignore: ':hidden',		
	//ignore: ".ignore",
	rules: {
		"qty[]": {
			//minlength: 1,
			number: true,
		},
	},
	beforeSend: function(){
		$('#ajax-spinner').show();
	},
	submitHandler: function(form) {
		var formData = new FormData($('#productListForm')[0]);
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
					if(response.product_id != ''){
						swal('Error',response.msg,'error');
						return false;
					}else{
						swal({ title: "",text: response.msg, button: false, icon: 'error' })
						return false;
					}
				}
			}
		});
	}
});

});



function getCheckboxCheckUncheck(product_id,category_id){


	if(product_id != ''){

		var allow_dropship=$('#allow_dropship').val();
		var allow_buyin=$('#allow_buyin').val();

		$('.chk-'+category_id+'-'+product_id).change(function(){
		
			var $inputs = $('input.chk-'+category_id+'-'+product_id+':checkbox')
			if($(this).is(':checked')){

				var isAllBuyinChecked = 0;
				var isAllVirtualChecked = 0;
				var isAllDropshipChecked = 0;
				
				$(this).closest("tr").addClass('current-row');
				var rows_selected = $('.current-row').length;
				$('#item-selected-count').html(rows_selected);
				
				$('#checkedProduct_'+category_id+'_'+product_id).val(product_id).prop('checked', true);
				if($(this).attr('id') == 'buyin_'+category_id+'_'+product_id){
					$('#qty_'+category_id+'_'+product_id).prop('disabled', false);
					$('#qty_'+category_id+'_'+product_id).val(1);

					$(".buyin-checkbox").each(function() {
						if (!this.checked){
							isAllBuyinChecked = 1;
							return false;
						}
						
					});
		
					if (isAllBuyinChecked == 0) {

						$(".buyin_chk input[type=checkbox]").prop('checked',true);				
						$(".virtual_chk input[type=checkbox]").attr('checked',false);
						$(".dropship_chk input[type=checkbox]").attr('checked',false);
						$(".buyin_chk").addClass("buyin_selected");
						$(".virtual_chk").removeClass("virtual_selected");
						$(".dropship_chk").removeClass("dropship_selected");
					}else{

						$(".buyin_chk input[type=checkbox]").attr('checked',false);
						$(".virtual_chk input[type=checkbox]").attr('checked',false);
						$(".dropship_chk input[type=checkbox]").attr('checked',false);
						$(".buyin_chk").removeClass("buyin_selected");
						$(".virtual_chk").removeClass("virtual_selected");
						$(".dropship_chk").removeClass("dropship_selected");
					}
				}
				
				if($(this).attr('id') == 'virtual_'+category_id+'_'+product_id){
					$('#qty_'+category_id+'_'+product_id).prop('disabled', false);
					$('#qty_'+category_id+'_'+product_id).val('');
					$(".virtual-checkbox").each(function() {
						if (!this.checked){
							isAllVirtualChecked = 1;
							return false;
						}
						
					});
				

					if (isAllVirtualChecked == 0) {
						$(".buyin_chk input[type=checkbox]").attr('checked',false);
						$(".virtual_chk input[type=checkbox]").prop('checked',true);
						$(".dropship_chk input[type=checkbox]").attr('checked',false);
						$(".buyin_chk").removeClass("buyin_selected");
						$(".virtual_chk").addClass("virtual_selected");
						$(".dropship_chk").removeClass("dropship_selected");
					}else{
						$(".buyin_chk input[type=checkbox]").attr('checked',false);
						$(".virtual_chk input[type=checkbox]").attr('checked',false);
						$(".dropship_chk input[type=checkbox]").attr('checked',false);
						$(".buyin_chk").removeClass("buyin_selected");
						$(".virtual_chk").removeClass("virtual_selected");
						$(".dropship_chk").removeClass("dropship_selected");
					}
				}
				
				if($(this).attr('id') == 'dropship_'+category_id+'_'+product_id){
					$("#qty_"+category_id+'_'+product_id).val('');
					$('#qty_'+category_id+'_'+product_id).prop('disabled', true);

					$(".dropship-checkbox").each(function() {
						if (!this.checked){
							isAllDropshipChecked = 1;
							return false;
						}
						
					});

		
					if (isAllDropshipChecked == 0) {
						$(".buyin_chk input[type=checkbox]").attr('checked',false);
						$(".virtual_chk input[type=checkbox]").attr('checked',false);
						$(".dropship_chk input[type=checkbox]").prop('checked',true);
						$(".buyin_chk").removeClass("buyin_selected");
						$(".virtual_chk").removeClass("virtual_selected");
						$(".dropship_chk").addClass("dropship_selected");
					} else{

						$(".buyin_chk input[type=checkbox]").attr('checked',false);
						$(".virtual_chk input[type=checkbox]").attr('checked',false);
						$(".dropship_chk input[type=checkbox]").attr('checked',false);
						$(".buyin_chk").removeClass("buyin_selected");
						$(".virtual_chk").removeClass("virtual_selected");
						$(".dropship_chk").removeClass("dropship_selected");
					}
				}	

				
							
				$("#qtyError_"+category_id+'_'+product_id).html("");
				$inputs.not(this).prop('checked',false); // <-- disable all but checked one
				
				
				$('.trp-row').each(function( i ) {
					if($(this).hasClass('current-row')){
						
					}else{						
						$(this).find('.chk-line-'+product_id).prop('disabled',true);						
					}
				  });
			}else{

				$(".buyin_chk input[type=checkbox]").attr('checked',false);
				$(".virtual_chk input[type=checkbox]").attr('checked',false);
				$(".dropship_chk input[type=checkbox]").attr('checked',false);
				$(".buyin_chk").removeClass("buyin_selected");
				$(".virtual_chk").removeClass("virtual_selected");
				$(".dropship_chk").removeClass("dropship_selected");
				
				$(this).closest("tr").removeClass('current-row');
				var rows_selected = $('.current-row').length;
				$('#item-selected-count').html(rows_selected);
				$('.chk-line-'+product_id).prop('disabled',false);
				$inputs.prop('checked',false); // <--

				if(allow_dropship=='0' && allow_buyin=='0')
				{
					
					$('.buyin-checkbox').attr('disabled',true);
					$('.virtual-checkbox').attr('disabled',true);
					$('.dropship-checkbox').attr('disabled',true);
					
					
				}else if(allow_buyin=='1' && allow_dropship=='0'){

					
					$('.buyin-checkbox').attr('disabled',false);
					$('.virtual-checkbox').attr('disabled',false);
					$('.dropship-checkbox').attr('disabled',true);
					
					
				}else if(allow_buyin=='0' && allow_dropship=='1'){

					
					$('.buyin-checkbox').attr('disabled',true);
					$('.virtual-checkbox').attr('disabled',false);	
					$('.dropship-checkbox').attr('disabled',false);
				
					
				}else{

					$('.buyin-checkbox').attr('disabled',false);
					$('.virtual-checkbox').attr('disabled',false);
					$('.dropship-checkbox').attr('disabled',false);


				}

				$('#checkedProduct_'+category_id+'_'+product_id).val(product_id).prop('checked', false);
				$("#qty_"+category_id+'_'+product_id).val('');
				$("#qtyError_"+category_id+'_'+product_id).html("");
				$('#qty_'+category_id+'_'+product_id).prop('disabled', true);
			}
		});
	}
}

function getProductCheckUncheck(product_id,category_id){
	if(product_id != ''){

		var allow_dropship=$('#allow_dropship').val();
		var allow_buyin=$('#allow_buyin').val();
				
		$('#checkedProduct_'+category_id+'_'+product_id).change(function(){

			var $inputs = $('input.chk-'+category_id+'-'+product_id+':checkbox')
			if($(this).is(':checked')){ 

				var isAllBuyinChecked = 0;
				var isAllVirtualChecked = 0;
				var isAllDropshipChecked = 0;


				$(this).closest("tr").addClass('current-row');
				var rows_selected = $('.current-row').length;
				$('#item-selected-count').html(rows_selected);
				
				if(allow_dropship=='0' && allow_buyin=='0')
				{
					$('.buyin-checkbox').attr('disabled',true);
					$('.virtual-checkbox').attr('disabled',true);
					$('.dropship-checkbox').attr('disabled',true);
					$('#qty_'+category_id+'_'+product_id).prop('disabled', true);
					$('#qty_'+category_id+'_'+product_id).val('');
					
				}else if(allow_buyin=='1' && allow_dropship=='0'){

					$('.buyin-checkbox').attr('disabled',false);
					$('.virtual-checkbox').attr('disabled',false);
					$('.dropship-checkbox').attr('disabled',true);
					$('#buyin_'+category_id+'_'+product_id).prop('checked', true);
					$('#qty_'+category_id+'_'+product_id).val(1);
					$('#qty_'+category_id+'_'+product_id).prop('disabled', false);
					
				}else if(allow_buyin=='0' && allow_dropship=='1'){

					$('.buyin-checkbox').attr('disabled',true);
					$('.virtual-checkbox').attr('disabled',false);	
					$('.dropship-checkbox').attr('disabled',false);
					$('#dropship_'+category_id+'_'+product_id).prop('checked', true);
					$('#qty_'+category_id+'_'+product_id).val('');
					$('#qty_'+category_id+'_'+product_id).prop('disabled', true);
					
				}else{

					$('.buyin-checkbox').attr('disabled',false);
					$('.virtual-checkbox').attr('disabled',false);
					$('.dropship-checkbox').attr('disabled',false);
					$('#buyin_'+category_id+'_'+product_id).prop('checked', true);
					$('#qty_'+category_id+'_'+product_id).val(1);
					$('#qty_'+category_id+'_'+product_id).prop('disabled', false);

				}


				$(".buyin-checkbox").each(function() {
					if (!this.checked){
						isAllBuyinChecked = 1;
						return false;
					}
					
				});
				
				$(".virtual-checkbox").each(function() {
					if (!this.checked){
						isAllVirtualChecked = 1;
						return false;
					}
					
				});
				
				$(".dropship-checkbox").each(function() {
					if (!this.checked){
						isAllDropshipChecked = 1;
						return false;
					}
					
				});


				if (isAllBuyinChecked == 0) {

					$(".buyin_chk input[type=checkbox]").prop('checked',true);				
					$(".virtual_chk input[type=checkbox]").attr('checked',false);
					$(".dropship_chk input[type=checkbox]").attr('checked',false);
					$(".buyin_chk").addClass("buyin_selected");
					$(".virtual_chk").removeClass("virtual_selected");
					$(".dropship_chk").removeClass("dropship_selected");

				}else if (isAllVirtualChecked == 0) {

					$(".buyin_chk input[type=checkbox]").attr('checked',false);
					$(".virtual_chk input[type=checkbox]").prop('checked',true);
					$(".dropship_chk input[type=checkbox]").attr('checked',false);
					$(".buyin_chk").removeClass("buyin_selected");
					$(".virtual_chk").addClass("virtual_selected");
					$(".dropship_chk").removeClass("dropship_selected");
				}else if (isAllDropshipChecked == 0) {

					$(".buyin_chk input[type=checkbox]").attr('checked',false);
					$(".virtual_chk input[type=checkbox]").attr('checked',false);
					$(".dropship_chk input[type=checkbox]").prop('checked',true);
					$(".buyin_chk").removeClass("buyin_selected");
					$(".virtual_chk").removeClass("virtual_selected");
					$(".dropship_chk").addClass("dropship_selected");
				} else{

					$(".buyin_chk input[type=checkbox]").attr('checked',false);
					$(".virtual_chk input[type=checkbox]").attr('checked',false);
					$(".dropship_chk input[type=checkbox]").attr('checked',false);
					$(".buyin_chk").removeClass("buyin_selected");
					$(".virtual_chk").removeClass("virtual_selected");
					$(".dropship_chk").removeClass("dropship_selected");
				}	

				  
				  $('.trp-row').each(function( i ) {
					if($(this).hasClass('current-row')){
						
					}else{
						
						$(this).find('.chk-line-'+product_id).prop('disabled',true);
						
					}
				  });
			}else{

				$(".buyin_chk input[type=checkbox]").attr('checked',false);
				$(".virtual_chk input[type=checkbox]").attr('checked',false);
				$(".dropship_chk input[type=checkbox]").attr('checked',false);
				$(".buyin_chk").removeClass("buyin_selected");
				$(".virtual_chk").removeClass("virtual_selected");
				$(".dropship_chk").removeClass("dropship_selected");
				
				$(this).closest("tr").removeClass('current-row');
				var rows_selected = $('.current-row').length;
				$('#item-selected-count').html(rows_selected);
				$('.chk-line-'+product_id).prop('disabled',false);
				
				$inputs.prop('checked',false); 
				if(allow_dropship=='0' && allow_buyin=='0')
				{
					$('.buyin-checkbox').attr('disabled',true);
					$('.virtual-checkbox').attr('disabled',true);
					$('.dropship-checkbox').attr('disabled',true);
					
					
				}else if(allow_buyin=='1' && allow_dropship=='0'){
					$('.buyin-checkbox').attr('disabled',false);
					$('.virtual-checkbox').attr('disabled',false);
					$('.dropship-checkbox').attr('disabled',true);
					
					
				}else if(allow_buyin=='0' && allow_dropship=='1'){
					$('.buyin-checkbox').attr('disabled',true);
					$('.virtual-checkbox').attr('disabled',false);	
					$('.dropship-checkbox').attr('disabled',false);
				
					
				}else{
					$('.buyin-checkbox').attr('disabled',false);
					$('.virtual-checkbox').attr('disabled',false);
					$('.dropship-checkbox').attr('disabled',false);


				}
				
				$("#qty_"+category_id+'_'+product_id).val('');
				$("#qtyError_"+category_id+'_'+product_id).html("");
				$('#qty_'+category_id+'_'+product_id).prop('disabled', true);
				
				
			}
		})
	}
}




function checkQty(value,product_id,category_id){
	
}

function validateQty()
{
	$( ".qty-table-box" ).each(function( i ) {
		if ( this.style.color !== "blue" ) {
		  this.style.color = "blue";
		} else {
		  this.style.color = "";
		}
	  });
}

function change_show_flag(show_flag,page){

	$('#show_flag').val(show_flag);

	if(show_flag == 1){
		
		$('#show-all-btn').removeClass('white-btn').addClass('purple-btn');
		$('#show-limited-btn').removeClass('purple-btn').addClass('white-btn');

	}else{
		$('#show-limited-btn').removeClass('white-btn').addClass('purple-btn');
		$('#show-all-btn').removeClass('purple-btn').addClass('white-btn');


	}

	var shopid = $("#shop_id").val();
	var category_ids = $("#category_ids").val();
	var draft_id = $("#draft_id").val();
	var saved_order_id = $("#saved_order_id").val();

	if(page == 'add'){
		FilterCatProductsDataTable(shopid,category_ids,show_flag);
	}else{
		FilterEditCatProductsDataTable(shopid,category_ids,show_flag,draft_id,saved_order_id);
	}
	
}


function FilterCatProductsDataTable(shopid,category_ids,show_flag){

    $("#productList_All").dataTable().fnDestroy();

    //datatables
    $('#productList_All').DataTable({ 
    	//"scrollY":        500,
    	//"scrollCollapse": true,
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "scrollX":false,
        "bInfo" : false,
        "stateSave" : true,
        "order": [], //Initial no order.
        "iDisplayLength": -1,
        "pageLength": -1,
        "searchDelay": 2000,
        "lengthMenu": [[10, 25, 50, 100, 200, 500, -1], [10, 25, 50, 100, 200, 500, "All"]],
        "searching": true,
        "bLengthChange": false,
		"bPaginate":false,
		'aoColumnDefs': [{
			'bSortable': false,
			'aTargets': ['nosort']
		}],
		"columnDefs": [
			{ "width": "70px", "targets": 11 }
		  ],
        // Load data for the table's content from an Ajax source
        "ajax": {
            "url": BASE_URL+"SupplierController/fetchAllProductsByCategory",
            "type": "POST",
			"data": {		 
                shop_id:shopid,
				category_ids:category_ids,
				show_flag:show_flag,                
                },
        },
		
        "search": {
                "caseInsensitive": false
        },
        "fnDrawCallback": function(oSettings) {
                
                    if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {
                        
                        $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
                        $(oSettings.nTableWrapper).find('.dataTables_length').hide();
                        
                    }else{
                        $(oSettings.nTableWrapper).find('.dataTables_paginate').show();
                        $(oSettings.nTableWrapper).find('.dataTables_length').show();
                    }
                    
                },
        "language": {                
            "infoFiltered": "",
            "search": '',
            "emptyTable":     "No data available in table",
            "searchPlaceholder": "Search",
            "paginate": {
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'  
            }

        },
        "initComplete": function() {
                
        }
            

    });
    $("#productList_All").on( 'init.dt', function () {

    } );
        

}


function FilterEditCatProductsDataTable(shopid,draft_id,saved_order_id,show_flag,stock_status,launch_date='',category_ids='',gender=''){

   // $("#EditproductList_All").dataTable().fnDestroy();

    var rows_selected = $('.current-row').length;
	$('#item-selected-count').html(rows_selected);

    //datatables
    $('#EditproductList_All').DataTable({ 
    	
		"destroy" : true,
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "scrollX":false,
        "bInfo" : false,
        "stateSave" : true,
        "order": [], //Initial no order.
        "iDisplayLength": -1,
        "pageLength": -1,
        "searchDelay": 2000,
        "lengthMenu": [[10, 25, 50, 100, 200, 500, -1], [10, 25, 50, 100, 200, 500, "All"]],
        "searching": true,
        "bLengthChange": false,
		"bPaginate":false,
		
		"columnDefs": [
			{ 
				"width": "70px", 
				"targets": 11 
			},
			{
				"orderable": true,
				"targets": 7,
			},
			{
			"orderable": false,
			"targets": "nosort"
			}
		],
		"ordering": true,
        
        // Load data for the table's content from an Ajax source
        "ajax": {
            "url": BASE_URL+"SupplierNewController/fetchAllEditedProductsByCategory",
            "type": "POST",
			"data": {		 
				shop_id:shopid,
				draft_id:draft_id,
				saved_order_id:saved_order_id,
				show_flag:show_flag,
				stock_status:stock_status,
				launch_date:launch_date, 
				category_ids:category_ids,
				gender:gender         
                },
        },
		
        "search": {
                "caseInsensitive": false
        },
        "fnDrawCallback": function(oSettings) {

					if(oSettings.json.select_row != ''){
						$(oSettings.json.select_row).each(function( index , data) {
							console.log( index+" == "+ data );
							$(data).closest("tr").addClass("current-row");
							
						});


					}
					var rows_selected = $('.current-row').length;
					$('#item-selected-count').html(rows_selected);
                
                    if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {
                        
                        $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
                        $(oSettings.nTableWrapper).find('.dataTables_length').hide();
                        
                    }else{
                        $(oSettings.nTableWrapper).find('.dataTables_paginate').show();
                        $(oSettings.nTableWrapper).find('.dataTables_length').show();
                    }
                    
        },
        "language": {                
            "infoFiltered": "",
            "search": '',
            "emptyTable":     "No data available in table",
            "searchPlaceholder": "Search",
            "paginate": {
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'  
            }

        },
        "initComplete": function() {
                
        }
            

    });
    $("#EditproductList_All").on( 'init.dt', function () {

    } );
        

}



$('.buyin_chk').change(function(event) {

	if($(".supplierProductList thead th .checkbox ").hasClass("virtual_selected")){
		$('.virtual_chk').click();
	}
	
	if($(".supplierProductList thead th .checkbox ").hasClass("dropship_selected")){
		$('.dropship_chk').click();
	}

	var allow_dropship=$('#allow_dropship').val();
	var allow_buyin=$('#allow_buyin').val();

	if($(this).hasClass("buyin_selected")){

		
		$(".supplierProductList tbody tr").removeClass('current-row');
		var rows_selected = $('.current-row').length;
		$('#item-selected-count').html(rows_selected);
		$('.buyin-checkbox:checkbox').prop('checked', false);
		$('.main-checkbox').prop('checked', false);
		$(this).removeClass("buyin_selected");

		if(allow_dropship=='0' && allow_buyin=='0')
		{
			$('.buyin-checkbox').attr('disabled',true);
			$('.virtual-checkbox').attr('disabled',true);
			$('.dropship-checkbox').attr('disabled',true);
			
		}else if(allow_buyin=='1' && allow_dropship=='0'){
			$('.buyin-checkbox').attr('disabled',false);
			$('.virtual-checkbox').attr('disabled',false);
			$('.dropship-checkbox').attr('disabled',true);
			
			
			
		}else if(allow_buyin=='0' && allow_dropship=='1'){
			$('.buyin-checkbox').attr('disabled',true);
			$('.virtual-checkbox').attr('disabled',false);	
			$('.dropship-checkbox').attr('disabled',false);
			
			
		}else{
			$('.buyin-checkbox').attr('disabled',false);
			$('.virtual-checkbox').attr('disabled',false);
			$('.dropship-checkbox').attr('disabled',false);
			

		}
		$('.qty-table-box').val('');
		$('.qty-table-box').prop('disabled', true);
	}else{

		$(".supplierProductList tbody tr").addClass('current-row');
		var rows_selected = $('.current-row').length;
		$('#item-selected-count').html(rows_selected);
		$('.buyin-checkbox:checkbox').prop('checked', true);
		$('.virtual-checkbox:checkbox').prop('checked', false);
		$('.dropship-checkbox:checkbox').prop('checked', false);	
		$('.main-checkbox').prop('checked', true);
		$(this).addClass("buyin_selected");
		$('.qty-table-box').val(1);

		$('.qty-table-box').prop('disabled', false);	
	}

	
});


$('.virtual_chk').change(function(event) {
	
	if($(".supplierProductList thead th .checkbox ").hasClass("buyin_selected")){
		$('.buyin_chk').click();
	}
	
	if($(".supplierProductList thead th .checkbox ").hasClass("dropship_selected")){
		$('.dropship_chk').click();
	}


	var allow_dropship=$('#allow_dropship').val();
	var allow_buyin=$('#allow_buyin').val();


	if($(this).hasClass("virtual_selected")){

		$(".supplierProductList tbody tr").removeClass('current-row');
		var rows_selected = $('.current-row').length;
		$('#item-selected-count').html(rows_selected);
		$('.virtual-checkbox:checkbox').prop('checked', false);	
		$('.main-checkbox').prop('checked', false);
		$(this).removeClass("virtual_selected");

		if(allow_dropship=='0' && allow_buyin=='0')
		{
			$('.buyin-checkbox').attr('disabled',true);
			$('.virtual-checkbox').attr('disabled',true);
			$('.dropship-checkbox').attr('disabled',true);
			
		}else if(allow_buyin=='1' && allow_dropship=='0'){
			$('.buyin-checkbox').attr('disabled',false);
			$('.virtual-checkbox').attr('disabled',false);
			$('.dropship-checkbox').attr('disabled',true);
			
			
		}else if(allow_buyin=='0' && allow_dropship=='1'){
			$('.buyin-checkbox').attr('disabled',true);
			$('.virtual-checkbox').attr('disabled',false);	
			$('.dropship-checkbox').attr('disabled',false);
			
			
		}else{
			$('.buyin-checkbox').attr('disabled',false);
			$('.virtual-checkbox').attr('disabled',false);
			$('.dropship-checkbox').attr('disabled',false);
			

		}
		$('.qty-table-box').val('');
		$('.qty-table-box').prop('disabled', true);
	}else{

		$(".supplierProductList tbody tr").addClass('current-row');
		var rows_selected = $('.current-row').length;
		$('#item-selected-count').html(rows_selected);
		$('.virtual-checkbox:checkbox').prop('checked', true);	
		$('.buyin-checkbox:checkbox').prop('checked', false);
		$('.dropship-checkbox:checkbox').prop('checked', false);
		$('.main-checkbox').prop('checked', true);
		$(this).addClass("virtual_selected");
		$('.qty-table-box').val('');
		$('.qty-table-box').prop('disabled', false);	
	}

	
});

$('.dropship_chk').change(function(event) {


	if($(".supplierProductList thead th .checkbox ").hasClass("buyin_selected")){
		$('.buyin_chk').click();
	}
	
	if($(".supplierProductList thead th .checkbox ").hasClass("virtual_selected")){
		$('.virtual_chk').click();
	}



	var allow_dropship=$('#allow_dropship').val();
	var allow_buyin=$('#allow_buyin').val();


	if($(this).hasClass("dropship_selected")){

		$(".supplierProductList tbody tr").removeClass('current-row');
		var rows_selected = $('.current-row').length;
		$('#item-selected-count').html(rows_selected);
		$('.dropship-checkbox:checkbox').prop('checked', false);	
		$('.main-checkbox').prop('checked', false);
		$(this).removeClass("dropship_selected");

		if(allow_dropship=='0' && allow_buyin=='0')
		{
			$('.buyin-checkbox').attr('disabled',true);
			$('.virtual-checkbox').attr('disabled',true);
			$('.dropship-checkbox').attr('disabled',true);
			
		}else if(allow_buyin=='1' && allow_dropship=='0'){
			$('.buyin-checkbox').attr('disabled',false);
			$('.virtual-checkbox').attr('disabled',false);
			$('.dropship-checkbox').attr('disabled',true);
			
			
		}else if(allow_buyin=='0' && allow_dropship=='1'){
			$('.buyin-checkbox').attr('disabled',true);
			$('.virtual-checkbox').attr('disabled',false);	
			$('.dropship-checkbox').attr('disabled',false);
			
			
		}else{
			$('.buyin-checkbox').attr('disabled',false);
			$('.virtual-checkbox').attr('disabled',false);
			$('.dropship-checkbox').attr('disabled',false);
			

		}
		$('.qty-table-box').val('');
		$('.qty-table-box').prop('disabled', true);
	}else{

		$(".supplierProductList tbody tr").addClass('current-row');
		var rows_selected = $('.current-row').length;
		$('#item-selected-count').html(rows_selected);
		$('.dropship-checkbox:checkbox').prop('checked', true);	
		$('.virtual-checkbox:checkbox').prop('checked', false);
		$('.buyin-checkbox:checkbox').prop('checked', false);
		$('.main-checkbox').prop('checked', true);
		$(this).addClass("dropship_selected");
		$('.qty-table-box').val('');
		$('.qty-table-box').prop('disabled', true);	
	}

	
});


