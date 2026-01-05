$(document).ready(function() {
  var col_show="";

  $("#DataTables_Product_Inventory_list").dataTable().fnDestroy();
  //datatables
  table = $('#DataTables_Product_Inventory_list').DataTable({ 
    
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
    "searchDelay": 200,
        "lengthMenu": [[25, 50, 100,200, 500, -1], [25, 50, 100,200, 500, "All"]],
        // Load data for the table's content from an Ajax source
        "ajax": {
          "url": BASE_URL+"Sellerproduct/loadproductInventoryAdjustmentAjax",
          "type": "POST",
          "data": function () {
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
      "searchPlaceholder": "Search",
      "paginate": {
        next: '<i class="fas fa-angle-right"></i>',
        previous: '<i class="fas fa-angle-left"></i>'  
      }

    },
    'columnDefs': [{
                "targets": [],
                "orderable": false
         }],
    "initComplete": function() {
         
    }
      

      });



    });