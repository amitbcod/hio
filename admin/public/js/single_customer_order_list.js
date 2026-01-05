console.log('loaded.........');
$(document).ready(function() {
	if ( $.fn.dataTable.isDataTable( '#DataTables_Table_Customer_order_list' ) ) {
		table = $('#DataTables_Table_Customer_order_list').DataTable({
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
						"searchPlaceholder": "Search or Scan barcode"
					}
		});
	}
	else {
		table = $('#DataTables_Table_Customer_order_list').DataTable( {
			"language":{  
						"paginate":{
							"previous": '<i class="fas fa-angle-left"></i>',
							"next": '<i class="fas fa-angle-right"></i>'
						},
						"search": "",
						"searchPlaceholder": "Search or Scan barcode"
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


$("#DataTables_Table_Customer_return_list").dataTable().fnDestroy();
table = $('#DataTables_Table_Customer_return_list').DataTable({ 
	"language":{  
				"paginate":{
					"previous": '<i class="fas fa-angle-left"></i>',
					"next": '<i class="fas fa-angle-right"></i>'
				},
				"search": "",
				"searchPlaceholder": "Search"
			},
	paging: true,
	searching: true,
	info: false,
	lengthChange: true,
	stateSave: true,	
	"order": [[ 0, "desc" ], [ 1, "desc" ]],
	"pageLength" : 5,
	"lengthMenu" : [[5, 10, 20, -1], [5, 10, 20, 'All']]

} );


// invoice save data
$(document).ready(function () {
	$("#invoice").click(function () {
		// console.log('test');
		// invoiceClass
		$('.orderClass').removeClass('active');   
		$('.invoiceClass').addClass('active');   
		$('#b2b-order-and-details').addClass('d-none');   
		$('#b2b-order-and-invoices').removeClass('d-none');  
		$('#return-order-details').addClass('d-none');
		$('.returnClass').removeClass('active'); 
	});
	$("#order").click(function () {
		// console.log('test');
		// invoiceClass
		$('.invoiceClass').removeClass('active');   
		$('#b2b-order-and-invoices').addClass('d-none'); 
		$('#b2b-order-and-details').removeClass('d-none'); 
		$('.orderClass').addClass('active'); 
		$('#return-order-details').addClass('d-none');
		$('.returnClass').removeClass('active'); 
	});

	$("#return-order").click(function () {
		//alert("order-return");
		$('.invoiceClass').removeClass('active');   
		$('#b2b-order-and-invoices').addClass('d-none'); 
		$('#b2b-order-and-details').addClass('d-none'); 
		$('.orderClass').removeClass('active');  
		$('.returnClass').addClass('active');
		$('#return-order-details').removeClass('d-none');
	});

	$("#WebCustomerInvoiceForm").submit(function(){
		dataString = $("#WebCustomerInvoiceForm").serialize();
		var invoiceTo=$("input[name='invoice_to']:checked").val();
		var alternateEmail=$( "#alternate_email" ).val();
		//console.log(alternateEmail);
		if(invoiceTo==1 && alternateEmail==''){
			$('.text-danger').html('Please select email id!');
			return false;
		}
		

		$.ajax({
			type: "POST",
			url: BASE_URL+"CustomerController/postwebshopCustomerInvoice",
			data: dataString,
			success: function(data){
				//swal("Success", response.message, "success");
				console.log(data);
				var obj = JSON.parse(data);
				swal("Success", data.msg, "success");
				window.location.href=BASE_URL+'CustomerController/customer_details/'+obj.customer_id;	                
			}

		});

		return false;  //stop the actual form post !important!

	});


});


function OpenEditPersonalInfoPopup(customer_id)
{	
	if(customer_id!='')
	{
		
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"CustomerController/OpenEditPersonalInfoPopup",
			data: {customer_id},
			//async:false,
			complete: function () { 
			},	
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},			
			success: function(response) {
				$("#FBCUserSecondaryModal").modal();
				 $("#modal-content-second").html(response);
			}
		});
	}	
}


function OpenEditAddressPopup(customer_id,address_id)
{	
	if(customer_id!='' && address_id !='')
	{
		
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"CustomerController/OpenEditAddressPopup",
			data: {customer_id,address_id},
			//async:false,
			complete: function () { 
			},	
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},			
			success: function(response) {
				$("#FBCUserSecondaryModal").modal();
				 $("#modal-content-second").html(response);
			}
		});
	}	
}