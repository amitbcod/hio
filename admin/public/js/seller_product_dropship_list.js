
	var save_method; //for save method string
            var table;
            var a = $(window).height(); // screen height  
            var b = 250;  var pageHeight =a-b;        
            if(pageHeight<200){          
              pageHeight=400;        
            }
			
		$(document).ready(function() {	
			FilterProductDataTable();
			
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


function FilterProductDataTable(){

	$('.filter-section').css('display','none');
	var schecked = []
	$("input[name='supplier_filter[]']:checked").each(function ()
	{
		schecked.push($(this).val());
	});

	var ichecked = []
	$("input[name='image_filter[]']:checked").each(function ()
	{
		ichecked.push($(this).val());
	});

	var price=$('#slider11').val();
	var inventory=$('#slider12').val();
	var supplier=schecked.join(',');
	var image_filter=ichecked.join(',');
	var from_date=$('#from_date').val();
	var to_date=$('#to_date').val();
	
  $("#DataTables_Table_WProductsDropship").dataTable().fnDestroy();
  //datatables
  table = $('#DataTables_Table_WProductsDropship').DataTable({ 
    //"scrollY":        pageHeight,
    "scrollCollapse": true,
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        //"scrollX":true,
		"bLengthChange" : true, //thought this line could hide the LengthMenu
		"bInfo":false,    
		"stateSave" : true,
        "order": [], //Initial no order.
        "iDisplayLength": 100,
		"pageLength": 100,
		"searchDelay": 2000,
        "lengthMenu": [[10, 25, 50, 100, 200, 500, -1], [10, 25, 50, 100, 200, 500, "All"]],
        // Load data for the table's content from an Ajax source
        "ajax": {
          "url": BASE_URL+"sellerproduct/loaddropshipproductsajax",
          "type": "POST",
          "data": function ( d ) {
            d.price = price;        
            d.inventory = inventory; 
            d.supplier = supplier;
			d.from_date = from_date;
			d.to_date = to_date;
			d.image_filter=image_filter;
          }
        },
		"search": {
				"caseInsensitive": false
		},
		"fnDrawCallback": function(oSettings) {												
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
		"initComplete": function() {
				 
		}
			
      });
}

