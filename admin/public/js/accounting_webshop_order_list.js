
var save_method; //for save method string
var table;
var a = $(window).height(); // screen height
var b = 250;  var pageHeight =a-b;
if(pageHeight<200){
	pageHeight=400;
}

$(document).ready(function() {
	$('[data-toggle="tooltip"]').tooltip();
	FilterWebshopDataTable();
	FilterB2WebshopDataTable();
	FilterInvoicingDataTable();
});


function FilterWebshopDataTable(){
	var schecked = []
	$("input[name='shipment_type[]']:checked").each(function ()
	{
		schecked.push($(this).val());
	});

	$("#DataTables_Table_AccountingWebshopOrders").dataTable().fnDestroy();
  	//datatables
  	table = $('#DataTables_Table_AccountingWebshopOrders').DataTable({

    	"scrollCollapse": true,
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "deferRender": true,
        //"scrollX":true,
		"bLengthChange" : false, //thought this line could hide the LengthMenu
		"bInfo":false,
		"stateSave" : true,
        "order": [], //Initial no order.
        "iDisplayLength": 25,
		"pageLength": 25,
		"searchDelay": 2000,
        "lengthMenu": [[25, 50, 100, 200, 500, -1], [25, 50, 100, 200, 500, "All"]],
        // Load data for the table's content from an Ajax source
        "ajax": {
          "url": BASE_URL+"AccountingWebshopOrdersController/loadordersajax",
          "type": "POST",
          "data": function ( d ) {


          }
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
			"searchPlaceholder": "Search",
			"paginate": {
			  next: '<i class="fas fa-angle-right"></i>',
			  previous: '<i class="fas fa-angle-left"></i>'
			}

		},
		'columnDefs': [{
                //"targets": [2,3,4],
                "targets": [0],
                "orderable": false
         }],
		"initComplete": function() {

		}


    });

    $("#DataTables_Table_AccountingWebshopOrders_not").dataTable().fnDestroy();
  	//datatables
  	table = $('#DataTables_Table_AccountingWebshopOrders_not').DataTable({

    	"scrollCollapse": true,
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "deferRender": true,
        //"scrollX":true,
		"bLengthChange" : false, //thought this line could hide the LengthMenu
		"bInfo":false,
		"stateSave" : true,
        "order": [], //Initial no order.
        "iDisplayLength": 25,
		"pageLength": 25,
		"searchDelay": 2000,
        "lengthMenu": [[25, 50, 100, 200, 500, -1], [25, 50, 100, 200, 500, "All"]],
        // Load data for the table's content from an Ajax source
        "ajax": {
          "url": BASE_URL+"AccountingWebshopOrdersController/loadordersajax_not",
          "type": "POST",
          "data": function ( d ) {


          }
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
			"searchPlaceholder": "Search",
			"paginate": {
			  next: '<i class="fas fa-angle-right"></i>',
			  previous: '<i class="fas fa-angle-left"></i>'
			}

		},
		'columnDefs': [{
                //"targets": [2,3,4],
                "targets": [0],
                "orderable": false
         }],
		"initComplete": function() {

		}


    });


}


function FilterB2WebshopDataTable(){

  $("#DataTables_Table_AccountingB2WebshopOrders").dataTable().fnDestroy();
  //datatables
  table = $('#DataTables_Table_AccountingB2WebshopOrders').DataTable({

    "scrollCollapse": true,
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        //"scrollX":true,
		"bLengthChange" : false, //thought this line could hide the LengthMenu
		"bInfo":false,
		"stateSave" : true,
        "order": [], //Initial no order.
        "iDisplayLength": 25,
		"pageLength": 25,
		"searchDelay": 2000,
        "lengthMenu": [[10, 25, 50, 100, 200, 500, -1], [10, 25, 50, 100, 200, 500, "All"]],
        // Load data for the table's content from an Ajax source
        "ajax": {
          "url": BASE_URL+"AccountingWebshopOrdersController/loadordersB2Webshopajax",
          "type": "POST",
          "data": function ( d ) {


          }
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
			"searchPlaceholder": "Search",
			"paginate": {
			  next: '<i class="fas fa-angle-right"></i>',
			  previous: '<i class="fas fa-angle-left"></i>'
			}

		},
		'columnDefs': [{
                //"targets": [2,3,4],
                // "targets": [4,5,6],
                "targets": [0],
                "orderable": false
         }],
		"initComplete": function() {

		}


      });
}


/*invoicing*/
function FilterInvoicingDataTable(){

  $("#DataTables_Table_AccountingInvoicingOrders").dataTable().fnDestroy();
  //datatables
  table = $('#DataTables_Table_AccountingInvoicingOrders').DataTable({

    "scrollCollapse": true,
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        //"scrollX":true,
		"bLengthChange" : false, //thought this line could hide the LengthMenu
		"bInfo":false,
		"stateSave" : true,
        "order": [], //Initial no order.
        "iDisplayLength": 25,
		"pageLength": 25,
		"searchDelay": 2000,
        "lengthMenu": [[10, 25, 50, 100, 200, 500, -1], [10, 25, 50, 100, 200, 500, "All"]],
        // Load data for the table's content from an Ajax source
        "ajax": {
          "url": BASE_URL+"AccountingWebshopOrdersController/loadinvoicingsajax",
          "type": "POST",
          "data": function ( d ) {


          }
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
			"searchPlaceholder": "Search",
			"paginate": {
			  next: '<i class="fas fa-angle-right"></i>',
			  previous: '<i class="fas fa-angle-left"></i>'
			}

		},
		'columnDefs': [{
                //"targets": [2,3,4],
                // "targets": [4,5,6],
                "orderable": false
         }],
		"initComplete": function() {

		}


      });
}


// download all invoice csv

function exportAllInvoice(type=""){
	//alert('teszt');
  if(type!='')
  {
    // console.log(type);
    $.ajax({
      type: "POST",
      dataType: "html",
      url: BASE_URL+"AccountingWebshopOrdersController/downlaodInvoiceAllCsv",
      data: {type:type},
      async:false,
      complete: function () {
      },
      beforeSend: function(){
         $('#ajax-spinner').show();
      },
      success: function(response) {
         $('#ajax-spinner').hide();
        if(response!='error'){

          $("#InvoicingCommonModal").modal();
          $("#modal-content").html(response);
        }else{

        }

      }
    });
  }
  else{

    return false;
  }
}

function DownloadInvoicingCSV()
{
    window.location.href=BASE_URL+'AccountingWebshopOrdersController/downloadinvocingcsv';
    $("#InvoicingCommonModal").modal('hide');
}


// invoice biil now check box
$(document).ready(function() {
	    $('#checkAll').change(function(e) {
	    	//alert('test');
	    	e.preventDefault();
	        if ($(this).prop('checked')) {
	            // alert("You have elected to show your checkout history."); //checked
	            $(".check_all").prop('checked', true);
	        }
	        else {
	            // alert("You have elected to turn off checkout history."); //not checked
	            $(".check_all").prop('checked', false);
	        }

	    });
	});
// function checkAll


$("#invoiceListForm").validate({

        ignore: ':hidden',
        //ignore: ".ignore",
        rules: {
            "check_id[]": {
				//minlength: 1,
                //required: true,
            },
        },
		beforeSend: function(){
			$('#ajax-spinner').show();
		},
        submitHandler: function(form) {
			//$('#product_list_next_btn').attr('disabled',true);
			var formData = new FormData($('#invoiceListForm')[0]);

        //console.log(formData);
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
                        swal({ title: "",text: response.msg, button: false, icon: 'success' })
						//$('#search-tab').html(response.msg);
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
	                    return false;
                    }
                }
            });
        }
    });


	function reSendInvoice(id,invoice_file)
	{
		//alert(invoice_file);
		if(id!='')
		{
			$.ajax({
				type: "POST",
				dataType: "json",
				url: BASE_URL+"AccountingWebshopOrdersController/resendInvoice",
				data: {id:id,invoice_file:invoice_file},
				async:false,
				complete: function () {
				},
				beforeSend: function(){
					$('#ajax-spinner').show();
				},
				success: function(response) {
					$('#ajax-spinner').hide();
					if (response.flag == 1) {
                        swal({ title: "",text: response.msg, button: false, icon: 'success' })
						setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
	                    return false;
                    }
				}
			});
		}else{
			return false;
		}
	}
