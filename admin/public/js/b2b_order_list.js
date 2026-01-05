
var save_method; //for save method string
var table;
var a = $(window).height(); // screen height
var b = 250;  var pageHeight =a-b;
if(pageHeight<200){
	pageHeight=400;
}

$(document).ready(function() {
	FilterOrdersDataTable();
	FilterOrdersDataTablePublisher();

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

$('#global-b2b-order-search').keypress(function(event){
	var keycode = (event.keyCode ? event.keyCode : event.which);
	if(keycode == '13'){
		var increment_id = $(this).val()
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: BASE_URL+'B2BOrdersController/GetOrderByIncrementId',
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

function FilterOrdersDataTable() {

    var current_tab  = $('#current_tab').val();

    if (current_tab === 'delivery-orders') {
        return; // do nothing, data already loaded from controller
    }

    var schecked = [];
    $("input[name='shipment_type[]']:checked").each(function () {
        schecked.push($(this).val());
    });

    var price        = $('#slider11').val();
    var shipment_type= schecked.join(',');
    var from_date    = $('#from_date').val();
    var to_date      = $('#to_date').val();

    var order_status = $('#order_status').val();



    $("#DataTables_Table_B2BOrders").dataTable().fnDestroy();

    // Base columns (always visible)
    var columns = [
        { "data": 0 }, // Order no
        { "data": 1 }, // Webshop order no
        { "data": 2 }, // Date/time
        { "data": 3 }, // Customer
        { "data": 4 }, // Merchant
        { "data": 5 }, // Order Status
    ];

    // Conditional columns based on tab
    if (current_tab === 'pickup-orders') {
        columns.push(
            { "data": 6 }, // Pickup Status
            { "data": 7 }, // Details
            { "data": 8 }  // Pickup Actions
        );
    } else if (current_tab === 'delivery-orders') {
        columns.push(
            { "data": 6 }, // Delivery Status
            { "data": 7 }, // Details
            { "data": 8 }  // Delivery Actions
        );
    } else {
        // Other tabs → only details
        columns.push({ "data": 6 }); // Details
    }

    table = $('#DataTables_Table_B2BOrders').DataTable({
        "scrollCollapse": true,
        "processing": true,
        "serverSide": true,
        "bLengthChange": true,
        "bInfo": false,
        "stateSave": false,
        "order": [],
        "iDisplayLength": 25,
        "pageLength": 25,
        "searchDelay": 2000,
        "lengthMenu": [[25, 50, 100, 200, 500, -1], [25, 50, 100, 200, 500, "All"]],
        "ajax": {
            "url": BASE_URL + "B2BOrdersController/loadordersajax",
            "type": "POST",
            "data": function (d) {
                d.price        = price;
                d.shipment_type= shipment_type;
                d.from_date    = from_date;
                d.to_date      = to_date;
                d.current_tab  = current_tab;
                d.order_status = order_status;
            }
        },
        "columns": columns,
        "columnDefs": [
            {
                "targets": (current_tab === 'pickup-orders' || current_tab === 'delivery-orders') ? [6, 8] : [],
                "orderable": false
            },
            {
                "targets": "_all",
                "className": "text-center"
            }
        ],
        "fnDrawCallback": function (oSettings) {
            if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {
                $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
            } else {
                $(oSettings.nTableWrapper).find('.dataTables_paginate').show();
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
        }
    });
}


function FilterOrdersDataTablePublisher() {
    var from_date   = $('#from_date').val();
    var to_date     = $('#to_date').val();
    var current_tab = $('#current_tab').val();
    var order_status= $('#order_status').val();

    $("#DataTables_Table_B2BOrderspublisher").dataTable().fnDestroy();

    // Base columns (always visible)
    var columns = [
        { "data": 0 }, // Order no
        { "data": 1 }, // Webshop order no
        { "data": 2 }, // Date/time
        { "data": 3 }, // Customer
        { "data": 4 }, // Merchant
        { "data": 5 }, // Order Status
    ];

    // Conditional columns based on tab
    if (current_tab === 'pickup-orders') {
        columns.push(
            { "data": 6 }, // Pickup Status
            { "data": 7 }, // Details
            { "data": 8 }  // Pickup Actions
        );
    } else if (current_tab === 'delivery-orders') {
        columns.push(
            { "data": 6 }, // Delivery Status
            { "data": 7 }, // Details
            { "data": 8 }  // Delivery Actions
        );
    } else {
        // Other tabs → only details
        columns.push({ "data": 6 }); // Details
    }

    table = $('#DataTables_Table_B2BOrderspublisher').DataTable({
        "scrollCollapse": true,
        "processing": true,
        "serverSide": true,
        "bLengthChange": true,
        "bInfo": false,
        "stateSave": false,
        "order": [],
        "iDisplayLength": 25,
        "pageLength": 25,
        "searchDelay": 2000,
        "lengthMenu": [[25, 50, 100, 200, 500, -1], [25, 50, 100, 200, 500, "All"]],
        "ajax": {
            "url": BASE_URL + "B2BOrderspublisherController/loadordersajax",
            "type": "POST",
            "data": function (d) {
                d.from_date    = from_date;
                d.to_date      = to_date;
                d.current_tab  = current_tab;
                d.order_status = order_status;
            }
        },
        "columns": columns,
        "columnDefs": [
            {
                "targets": (current_tab === 'pickup-orders' || current_tab === 'delivery-orders') ? [6, 8] : [],
                "orderable": false
            },
            {
                "targets": "_all",
                "className": "text-center"
            }
        ],
        "fnDrawCallback": function (oSettings) {
            if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {
                $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
            } else {
                $(oSettings.nTableWrapper).find('.dataTables_paginate').show();
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
        }
    });
}


function AssignNewDeliveryPopup(order_id, attempt_no) {
    if (order_id !== '') {
        attempt_no = (typeof attempt_no !== 'undefined' && attempt_no > 0) ? attempt_no : 1;

        $.ajax({
            url: BASE_URL + "B2BOrdersController/AssignNewDeliveryPopup",
            type: "POST",
            data: { order_id: order_id, attempt_no: attempt_no },
            success: function(response) {
                if (response !== 'error') {
                    $("#FBCUserCommonModal").modal();
                    $("#modal-content").html(response);
                } else {
                    Swal.fire("Error", "Could not load Assign Delivery popup", "error");
                }
            }
        });
    }
}

function AssignNewPickupPopup(order_id) {
    if (order_id !== '') {
        $.ajax({
            url: BASE_URL + "B2BOrdersController/AssignNewPickupPopup",
            type: "POST",
            data: { order_id: order_id },
            success: function(response) {
                if (response !== 'error') {
                    $("#FBCUserCommonModal").modal();
                    $("#modal-content").html(response);
                } else {
                    Swal.fire("Error", "Could not load Assign Pickup popup", "error");
                }
            }
        });
    }
}

/*function MarkPickupReceived(order_id) {
    if (order_id !== '') {
        $.ajax({
            url: BASE_URL + "B2BOrdersController/MarkPickupReceived",
            type: "POST",
            data: { order_id: order_id },
            dataType: "json",
            success: function(res) {
                Swal.fire({
                    title: res.status == 200 ? "Success" : "Error",
                    text: res.message,
                    icon: res.status == 200 ? "success" : "error"
                }).then(() => {
                    if (res.status == 200) location.reload();
                });
            },
            error: function() {
                Swal.fire("Error", "Something went wrong. Please try again.", "error");
            }
        });
    }
}*/

function MarkPickupReceived(order_id) {
    if (order_id !== '') {
        $.ajax({
            url: BASE_URL + "B2BOrdersController/MarkPickupReceived",
            type: "POST",
            data: { order_id: order_id },
            success: function(response) {
                var res = jQuery.parseJSON(response);
                swal({
                    title: res.status == 200 ? "Success" : "Error",
                    icon: res.status == 200 ? "success" : "error",
                    text: res.message,
                    buttons: true,
                }, function() {
                    if (res.status == 200) location.reload();
                });
            }
        });
    }
}

function submitMarkFailedForm() {
    var form = $('#mark_failed_form');

    $.ajax({
        url: BASE_URL + "B2BOrdersController/MarkAsFailed",
        type: "POST",
        data: form.serialize(),
        dataType: "json",
        success: function(res) {
            $('#modal').modal('hide');
            Swal.fire({
                title: res.status == 200 ? "Success" : "Error",
                text: res.message,
                icon: res.status == 200 ? "success" : "error"
            }).then(() => location.reload());
        },
        error: function() {
            Swal.fire("Error", "Something went wrong. Please try again.", "error");
        }
    });

    return false;
}

function MarkAsFailedPopup(order_id, attempt_no) {
    if (order_id !== '') {
        $.ajax({
            url: BASE_URL + "B2BOrdersController/MarkAsFailedPopup",
            type: "POST",
            data: { order_id: order_id, attempt_no: attempt_no },
            success: function(response) {
                if (response !== 'error') {
                    $("#FBCUserCommonModal").modal();
                    $("#modal-content").html(response);
                } else {
                    Swal.fire("Error", "Something went wrong.", "error");
                }
            },
            error: function() {
                Swal.fire("Error", "Something went wrong. Please try again.", "error");
            }
        });
    } else {
        return false;
    }
}


/*function AssignNewDeliveryPopup(order_id, attempt_no) {
    if (order_id !== '') {
        // If attempt_no is not defined or 0, set it to 1 for first-time delivery
        attempt_no = (typeof attempt_no !== 'undefined' && attempt_no > 0) ? attempt_no : 1;

        $.ajax({
            url: BASE_URL + "B2BOrdersController/AssignNewDeliveryPopup",
            type: "POST",
            data: { order_id: order_id, attempt_no: attempt_no },
            success: function(response) {
                if (response !== 'error') {
                    $("#FBCUserCommonModal").modal();
                    $("#modal-content").html(response);
                } else {
                    swal("Error", "Could not load Assign Delivery popup", "error");
                }
            }
        });
    }
}

function AssignNewPickupPopup(order_id) {
    if (order_id !== '') {
        $.ajax({
            url: BASE_URL + "B2BOrdersController/AssignNewPickupPopup",
            type: "POST",
            data: { order_id: order_id },
            success: function(response) {
                if (response !== 'error') {
                    $("#FBCUserCommonModal").modal();
                    $("#modal-content").html(response);
                } else {
                    swal("Error", "Could not load Assign Pickup popup", "error");
                }
            }
        });
    }
}


function MarkPickupReceived(order_id) {
    if (order_id !== '') {
        $.ajax({
            url: BASE_URL + "B2BOrdersController/MarkPickupReceived",
            type: "POST",
            data: { order_id: order_id },
            success: function(response) {
                var res = jQuery.parseJSON(response);
                swal({
                    title: res.status == 200 ? "Success" : "Error",
                    icon: res.status == 200 ? "success" : "error",
                    text: res.message,
                    buttons: true,
                }, function() {
                    if (res.status == 200) location.reload();
                });
            }
        });
    }
}

	function submitMarkFailedForm() {
    var form = $('#mark_failed_form');
    $.ajax({
        url: BASE_URL + "B2BOrdersController/MarkAsFailed",
        type: "POST",
        data: form.serialize(),
        success: function(response) {
            $('#modal').modal('hide');
            var res = jQuery.parseJSON(response);

            swal({
                title: res.status == 200 ? "Success" : "Error",
                icon: res.status == 200 ? "success" : "error",
                text: res.message,
                buttons: true,
            }, function() {
                location.reload();
            });
        }
    });
    return false;
}

function MarkAsFailedPopup(order_id, attempt_no) {
    if (order_id != '') {
        $.ajax({
            url: BASE_URL + "B2BOrdersController/MarkAsFailedPopup",
            type: "POST",
            data: {
                order_id: order_id,
                attempt_no: attempt_no
            },
            success: function(response) {
                if (response != 'error') {
                    $("#FBCUserCommonModal").modal();
                    $("#modal-content").html(response);
                } else {
                    swal("Error", "Something went wrong.", "error");
                }
            }
        });
    } else {
        return false;
    }
}*/


/*function markAsCollected(orderId) {
    swal({
        title: "Are you sure?",
        text: "Do you really want to mark this order as collected?",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    },
    function(willCollect) {   // <-- v1 uses callback here
        if (willCollect) {
            $.ajax({
                url: BASE_URL + "B2BOrdersController/markCollected",
                type: "POST",
                data: { order_id: orderId },
                success: function(response) {
                    var res = jQuery.parseJSON(response);

                    swal({
                        title: res.status == 200 ? "Success" : "Error",
                        icon: res.status == 200 ? "success" : "error",
                        text: res.message,
                        buttons: true,
                    }, function() {
                        if (res.status == 200) {
                            location.reload();
                        }
                    });
                },
                error: function() {
                    swal("Error", "Something went wrong. Please try again.", "error");
                }
            });
        }
    });
}

function markAsDelivered(orderId) {
    swal({
        title: "Are you sure?",
        text: "Do you really want to mark this order as delivered?",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    },
    function(willCollect) {   // <-- v1 uses callback here
        if (willCollect) {
            $.ajax({
                url: BASE_URL + "B2BOrdersController/markDelivered",
                type: "POST",
                data: { order_id: orderId },
                success: function(response) {
                    var res = jQuery.parseJSON(response);

                    swal({
                        title: res.status == 200 ? "Success" : "Error",
                        icon: res.status == 200 ? "success" : "error",
                        text: res.message,
                        buttons: true,
                    }, function() {
                        if (res.status == 200) {
                            location.reload();
                        }
                    });
                },
                error: function() {
                    swal("Error", "Something went wrong. Please try again.", "error");
                }
            });
        }
    });
}*/

function markAsCollected(orderId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you really want to mark this order as collected?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, collect it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + "B2BOrdersController/markCollected",
                type: "POST",
                data: { order_id: orderId },
                dataType: "json",
                success: function(res) {
                    Swal.fire({
                        title: res.status == 200 ? 'Success' : 'Error',
                        text: res.message,
                        icon: res.status == 200 ? 'success' : 'error'
                    }).then(() => {
                        if (res.status == 200) location.reload();
                    });
                },
                error: function() {
                    Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                }
            });
        }
    });
}

function markAsDelivered(orderId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you really want to mark this order as delivered?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, deliver it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + "B2BOrdersController/markDelivered",
                type: "POST",
                data: { order_id: orderId },
                dataType: "json",
                success: function(res) {
                    Swal.fire({
                        title: res.status == 200 ? 'Success' : 'Error',
                        text: res.message,
                        icon: res.status == 200 ? 'success' : 'error'
                    }).then(() => {
                        if (res.status == 200) location.reload();
                    });
                },
                error: function() {
                    Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                }
            });
        }
    });
}


function openDeliveryPopup(order_id) {
    // Show modal
    $('#deliveryPopupModal').modal('show');

    // Show loading text
    $('#deliveryPopupContent').html('<p class="text-center">Loading...</p>');

    // AJAX request to fetch delivery data
    $.ajax({
        url: BASE_URL + "B2BOrdersController/getDeliveryAttemptsPopup",
        type: "POST",
        data: { order_id: order_id },
        success: function(response) {
            $('#deliveryPopupContent').html(response);
        },
        error: function() {
            $('#deliveryPopupContent').html('<p class="text-danger text-center">Failed to load delivery data.</p>');
        }
    });
}
