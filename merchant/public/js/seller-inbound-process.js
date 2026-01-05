var save_method; //for save method string
	var table;
	var a = $(window).height(); // screen height  
	var b = 250;  var pageHeight =a-b;        
	if(pageHeight<200){          
	  pageHeight=400;        
	}


    if($('#product_label').is(':checked')){
        
        $(".import-month").show();
    }else{
        $(".import-month").hide();
    } 

    function onlyNumberKey(evt) { 
      var ASCIICode = (evt.which) ? evt.which : evt.keyCode 
      if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57)) 
          return false; 
      return true; 
}


function ScanBarcodeManually(){
    $('#barcode-error').html('');
    $('#barcode_item').removeClass('error');

   if($('#warehouse_eta').length > 0){
          var warehouse_eta=$('#warehouse_eta').val();}
    else {var warehouse_eta=''; }

    if($('#carrier').length > 0){
          var carrier=$('#carrier').val();}
    else {var carrier=''; }

    if($('#carrier_reference').length > 0){
          var carrier_reference=$('#carrier_reference').val();}
    else {var carrier_reference=''; }

    if($('#carton_count').length > 0){
          var carton_count=$('#carton_count').val();}
    else {var carton_count=''; }

    if($('#po_reference').length > 0){
          var po_reference=$('#po_reference').val();}
    else {var po_reference=''; }

    var barcode_item=$('#barcode_item').val();
    var sku=$('#sku').val();
    var name=$('#name').val();
    var qty=$('#qty').val();
    var select_month=$('#select_month').val();
    var select_year=$('#select_year').val();
    var label_size=$('#label_size').val();

    if ($('#product_label').is(':checked')) { 
        var product_label = 1;
    }else{
        var product_label = 0;
    }

    if(name ==''){
        $('#barcode_item').addClass('error');
        $('#barcode-error').html('Please enter name.');    
        return false; 
    }

    if(barcode_item=='' && sku ==''){
        $('#barcode_item').addClass('error');
        $('#barcode-error').html('Please enter barcode/sku.');    
        return false;
    }	
    else{  
        $.ajax({ 
                url: BASE_URL+"InboundController/checkProduct",
                type: "POST",
                data: {	warehouse_eta:warehouse_eta,carrier:carrier,carrier_reference:carrier_reference,
                carton_count:carton_count,po_reference:po_reference,	 
                    barcode_code:barcode_item,
                    sku:sku, 
                    name:name,
                    qty:qty, 
                    product_label:product_label,
                    select_month:select_month,
                    select_year:select_year,
                    label_size:label_size

                },
                    
                success: function(response) {
                    
                    var obj = JSON.parse(response);
                    if(obj.status == 200) {
                        
                        play('beep-success');

                        sessionStorage.setItem("setBackgroundColor", 1);

                        
                        setTimeout(function() {
                            window.location.href = BASE_URL+'seller/inbound/view/'+obj.id;
                        }, 2000)

                        if(obj.printStatus == 1){

                            for (let i = 1; i <= obj.qty; i++) {

                                var mywindow = window.open('', 'PRINT'); 
                                var is_chrome = Boolean(mywindow.chrome);                
                                mywindow.document.write(obj.printHTML);
                               
                                if (is_chrome) {
                                    setTimeout(function () { // wait until all resources loaded 
                                            mywindow.document.close(); // necessary for IE >= 10
                                            mywindow.focus(); // necessary for IE >= 10
                                            mywindow.print();  // change window to winPrint
                                            mywindow.close();// change window to winPrint
                                    }, 1000);
                                }
                                else {
                                    mywindow.document.close(); // necessary for IE >= 10
                                    mywindow.focus(); // necessary for IE >= 10
                                    mywindow.print();
                                    mywindow.close();
                                }

                              }
                            
                        }

                        

                        return true;
                        
                    } else {
                        play('beep-error');
                        $('#barcode_item').addClass('error');
                        $('#barcode-error').html(obj.message);
                        return false;
                        
                    }
                    
                }
            });
    }
    
}    

function ScanInboundProductsManually(){

    $('#barcode-error').html('');
    $('#barcode_item').removeClass('error');

    var barcode_item=$('#barcode_item').val();
    var sku=$('#sku').val();
    var name=$('#name').val();
    var qty=$('#qty').val();
    var order_id = $('#order_id').val();
    var select_month=$('#select_month').val();
    var select_year=$('#select_year').val();
    var label_size=$('#label_size').val();

    if ($('#product_label').is(':checked')) { 
        var product_label = 1;
    }else{
        var product_label = 0;
    }

    if(name ==''){
        $('#barcode_item').addClass('error');
        $('#barcode-error').html('Please enter name.');    
        return false; 
    }

    if(barcode_item=='' && sku ==''){
        $('#barcode_item').addClass('error');
        $('#barcode-error').html('Please enter barcode/sku.');    
        return false;
    }	
    else{  
        $.ajax({ 
                url: BASE_URL+"InboundController/AddMoreProducts",
                type: "POST",
                data: {		 
                    barcode_code:barcode_item,
                    sku:sku, 
                    name:name,
                    qty:qty, 
                    product_label:product_label,
                    order_id:order_id,
                    select_month:select_month,
                    select_year:select_year,
                    label_size:label_size

                },
                    
                success: function(response) {
                    
                    var obj = JSON.parse(response);
                    if(obj.status == 200) {
                        
                        play('beep-success');
                      
                        $('#barcode_item').val('');
                        $('#sku').val('');
                        $('#qty').val(1);

                        $('#total-products').html(obj.total_products);
                        $('#total-price').html(obj.total_price);  
                        
                        if(obj.printStatus == 1){

                            for (let i = 1; i <= obj.qty; i++) {

                                var mywindow = window.open('', 'PRINT'); 
                                var is_chrome = Boolean(mywindow.chrome);

                                mywindow.document.write(obj.printHTML);

                                if (is_chrome) {
                                    setTimeout(function () { // wait until all resources loaded 
                                            mywindow.document.close(); // necessary for IE >= 10
                                            mywindow.focus(); // necessary for IE >= 10
                                            mywindow.print();  // change window to winPrint
                                            mywindow.close();// change window to winPrint
                                    }, 1000);
                                }
                                else {
                                    mywindow.document.close(); // necessary for IE >= 10
                                    mywindow.focus(); // necessary for IE >= 10
                                    mywindow.print();
                                    mywindow.close();
                                }

                              }
                            
                        }

                        FilterInboundProductDataTable(1);
                        return true;
                        
                    } else {
                        play('beep-error');
                        $('#barcode_item').addClass('error');
                        $('#barcode-error').html(obj.message);
                        return false;
                        
                    }
                    
                }
            });
    }

}


function FilterInboundDataTable(){

    $("#datatableInboundList").dataTable().fnDestroy();

    //datatables
    $('#datatableInboundList').DataTable({ 

        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "scrollX":false,
        "bInfo" : false,
        "stateSave" : true,
        "order": [], //Initial no order.
        "iDisplayLength": 25,
        "pageLength": 25,
        "searchDelay": 2000,
        "lengthMenu": [[5, 10, 25, 50, 100, 200, 500, -1], [5, 10, 25, 50, 100, 200, 500, "All"]],
        "searching": false,
        "bLengthChange": false,
        // Load data for the table's content from an Ajax source
        "ajax": {
            "url": BASE_URL+"InboundController/loadInboundajax",
            "type": "POST",
        },
        //Set column definition initialisation properties.
        "columnDefs": [
            { 
                "targets": [ 0 ], //first column / numbering column
                "orderable": false, //set not orderable
            },
        ],
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
    $("#datatableInboundList").on( 'init.dt', function () {

    } );
        

}


function FilterInboundProductDataTable(background){

	var order_id = $('#order_id').val();
    $("#datatableInboundProductList").dataTable().fnDestroy();

    //datatables
    $('#datatableInboundProductList').DataTable({ 
  
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
        "searching": false,
        "bLengthChange": false,
        // Load data for the table's content from an Ajax source
        "ajax": {
            "url": BASE_URL+"InboundController/loadInboundProductsajax",
            "type": "POST",
			data: { order_id :order_id},
        },
        //Set column definition initialisation properties.
        "columnDefs": [
        { 
            "targets": [ 0 ], //first column / numbering column
            "orderable": false, //set not orderable
        },
        ],
     
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
    $("#datatableInboundProductList").on( 'init.dt', function () {

        SessionBackgroundColor = sessionStorage.getItem("setBackgroundColor");
        if(background == 1 || SessionBackgroundColor == 1 ){
            $("#datatableInboundProductList tbody tr:first-child").css("background", "green");
            sessionStorage.removeItem("setBackgroundColor");
        }

    } );
        

}


function FilterInboundOrderedProductDataTable(order_id){
    
    $("#datatableInboundProductList").dataTable().fnDestroy();

    //datatables
    $('#datatableInboundProductList').DataTable({ 
 
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
        "searching": false,
        "bLengthChange": false,
        // Load data for the table's content from an Ajax source
        "ajax": {
            "url": BASE_URL+"InboundController/loadInboundOrderedProductsajax",
            "type": "POST",
            data: { order_id :order_id},
        },
        //Set column definition initialisation properties.
        "columnDefs": [
        { 
            "targets": [ 0 ], //first column / numbering column
            "orderable": false, //set not orderable
        },
        ],
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
    $("#datatableInboundProductList").on( 'init.dt', function () {

    } );
        

}

function deleteInboundProducts(product_id,order_id){

    $.ajax({ 
        url: BASE_URL+"InboundController/deleteProducts",
        type: "POST",
        data: {		 
            product_id:product_id,
            order_id:order_id, 
            table:table,


        },      
        success: function(response) {
            
            var obj = JSON.parse(response);
            if(obj.status == 200) {
                

                $('#total-products').html(obj.total_products);
                $('#total-price').html(obj.total_price);                       
                FilterInboundProductDataTable();
                return true;
                
            } 
            
        }
    });


}


function updateLocation(product_id){

    var order_id = $( "#order_id" ).val();
    var location = $( "#location_"+product_id).val();

    $.ajax({ 
        url: BASE_URL+"InboundController/updateLocation",
        type: "POST",
        data: {		 
            order_id:order_id,
            product_id:product_id, 
            location:location
        },
        success: function(response) {

            FilterInboundProductDataTable();


        }   
    });     
                  
    
  }


  
  function updateQtyScanned(product_id){

    var order_id = $( "#order_id" ).val();
    var qty_scanned = $( "#qty_scanned_"+product_id).val();

    $.ajax({ 
        url: BASE_URL+"InboundController/updateQtyScanned",
        type: "POST",
        data: {		 
            order_id:order_id,
            product_id:product_id, 
            qty_scanned:qty_scanned
        },
        success: function(response) {

            var obj = JSON.parse(response);
            if(obj.status == 200) {
                

                $('#total-products').html(obj.total_products);
                $('#total-price').html(obj.total_price);                       
                FilterInboundProductDataTable();
                return true;
                
            }


        }   
    });     
                  
    
  }


function play(flag) {
	if(flag == 'beep-success') { 
		var beepsound = new Audio(BASE_URL+'public/beepsounds/beep-success.wav'); 
	} else {
		var beepsound = new Audio(BASE_URL+'public/beepsounds/beep-error.wav');
	}
	beepsound.play(); 
}

function ExportDraftProducts(order_id){

    //alert("hi");
    inbound_export = $('#inbound_export').val();
    if(inbound_export == 'csv'){
        url = BASE_URL+"InboundController/exportCSV";
    }else{
        url = BASE_URL+"InboundController/exportExcel";
    }

    $.ajax({ 
        url: url,
        type: "POST",
        data: {		 
            order_id:order_id, 
        },      
        success: function(response) {
            
        }
    });


}

$('#product_label').change(function() {
    if($(this).is(':checked')){
        $('#select_month').val('');
        $('#select_year').val('');
        $('#label_size').val('');
        $(".import-month").show();
    }else{
        $('#select_month').val('');
        $('#select_year').val('');
        $('#label_size').val('');
        $(".import-month").hide();
    }
  });

function OpenApproveInbounndPopup(id)
{
    if(id!=''){
        $.ajax({ 
                url: BASE_URL+"InboundController/OpenApproveInbounndPopup",
                type: "POST",
                data: {
                  id:id
                },
                success: function(response) {
                    if(response!='error'){
                        $("#FBCUserCommonModal").modal();
                        $("#modal-content").html(response);
                    }else{
                        return false;
                    }
                    
                }
            });
    }else{
        return false;
    }
}


function ConfirmApproveInbound(id){
    if(id!=''){
        $('#conf-approve-btn').prop('disabled',true);
        
        $.ajax({ 
                url: BASE_URL+"InboundController/inbound_approve",
                type: "POST",
                data: {
                  id:id
                },
                success: function(response) {                   
                    var obj = JSON.parse(response);
                    if(obj.status == 200) {
                        $("#FBCUserCommonModal").modal('hide');
                        swal({ title: "",text: obj.message, button: false, icon: 'success' })
                        setTimeout(function() { window.location.reload(); }, 1000);
                        
                    } else {
                        $('#conf-approve-btn').prop('disabled',false);
                        swal({ title: "",text: obj.message, button: false, icon: 'error' })
                        setTimeout(function() { window.location.reload(); }, 1000);
                        
                    }
                    
                }
            });
        
    }else{
        return false;
    }
    
}



