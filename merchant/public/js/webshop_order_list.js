
	var save_method; //for save method string
            var table;
            var a = $(window).height(); // screen height
            var b = 250;  var pageHeight =a-b;
            if(pageHeight<200){
              pageHeight=400;
            }

		$(document).ready(function() {
			 $('[data-toggle="tooltip"]').tooltip();
			FilterOrdersDataTable();


			/*
			$(document).on("preInit.dt", function () {
				var $sb = $(".dataTables_filter input[type='search']");
				// remove current handler
				$sb.off();
				// Add key hander
				$sb.on("keypress", function (evtObj) {
					if (evtObj.keyCode == 13) {
						$('#DataTables_Table_WebshopOrders').DataTable().search($sb.val()).draw();
					}
				});
				// add button and button handler
				var btn = $("<button type='button' class='btn btn-primary btn-sm'>Go</button>");
				$sb.after(btn);
				btn.on("click", function (evtObj) {
					$('#DataTables_Table_WebshopOrders').DataTable().search($sb.val()).draw();
				});
			});

			FilterOrdersDataTable();
			*/


			$("#from_date").datepicker({
				format: "dd-mm-yyyy",
				minDate: 0,
				autoclose: true,
				todayHighlight: true,
				onSelect: function (selected) {
					var dt = new Date(selected);
					dt.setDate(dt.getDate() + 1);
					$("#to_date").datepicker("option", "minDate", dt);
				}
			});
			$('#to_date').datepicker({
				autoclose: true,
				todayHighlight: true,
				format: "dd-mm-yyyy"
			});

		});

$('#global-order-search').keypress(function(event){
	var keycode = (event.keyCode ? event.keyCode : event.which);
	if(keycode == '13'){
		var increment_id = $(this).val()
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: BASE_URL+'WebshopOrdersController/GetOrderByIncrementId',
			data: {increment_id:increment_id},
			success:function(response){
				if (response.flag == 1) {
					setTimeout(function() {
						window.location.href = response.redirect;
					}, 1000);
				}else{
					swal('Error',response.message,'error');
					return false;
				}
			}
		});
	}
});

function FilterOrdersDataTable(){


	var schecked = []
	$("input[name='shipment_type[]']:checked").each(function ()
	{
		schecked.push($(this).val());
	});


	var price=$('#slider11').val();
	var shipment_type=schecked.join(',');
	var from_date=$('#from_date').val();
	var to_date=$('#to_date').val();
	var current_tab=$('#current_tab').val();
	var order_status=$('#order_status').val();
	var is_warehouse=$('#is_warehouse').val();
	var col_show="";
	if(is_warehouse==1){col_show=[8,9]}else{col_show=[7,8]}

  $("#DataTables_Table_WebshopOrders").dataTable().fnDestroy();
  //datatables
  table = $('#DataTables_Table_WebshopOrders').DataTable({

    "scrollCollapse": true,
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        //"scrollX":true,
		"bLengthChange" : true, //thought this line could hide the LengthMenu
		"bInfo":false,
		"stateSave" : true,
        "order": [], //Initial no order.
        "iDisplayLength": 25,
		"pageLength": 25,
		"searchDelay": 2000,
        "lengthMenu": [[25, 50, 100, 200, 500, -1], [25, 50, 100, 200, 500, "All"]],
        // Load data for the table's content from an Ajax source
        "ajax": {
          "url": BASE_URL+"WebshopOrdersController/loadordersajax",
          "type": "POST",
          "data": function ( d ) {
			d.price=price;
			d.from_date = from_date;
			d.to_date = to_date;
			d.current_tab = current_tab;
			d.order_status = order_status;

          }
        },
		"search": {
				"caseInsensitive": false
		},
		"fnDrawCallback": function(oSettings) {

					if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {

						$(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
						// $(oSettings.nTableWrapper).find('.dataTables_length').hide();

					}else{
						$(oSettings.nTableWrapper).find('.dataTables_paginate').show();
						// $(oSettings.nTableWrapper).find('.dataTables_length').show();
					}

			  },
		"language": {
			"infoFiltered": "",
			"search": '',
			"searchPlaceholder": "Filter List",
			"paginate": {
			  next: '<i class="fas fa-angle-right"></i>',
			  previous: '<i class="fas fa-angle-left"></i>'
			}

		},
		'columnDefs': [{
                "targets": col_show,
                "orderable": false
         }],
		"initComplete": function() {

		}


      });
}

