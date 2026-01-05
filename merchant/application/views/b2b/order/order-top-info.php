<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
  <h1 class="head-name">Order Details </h1>
  <div class="float-right">
  <?php if ($OrderData->parent_id>0) {?>
		<button class="white-btn dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="View Split Orders">View Split Orders </button>
		<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
		<?php if (isset($SplitOrderIds) && count($SplitOrderIds)>0) {
			foreach ($SplitOrderIds as $spo) {?>
			<a id="" class="m-2 dropdown-item" href="<?php echo base_url() ?>b2b/split-order/detail/<?php echo $spo->order_id; ?>"><?php echo $spo->increment_id; ?></a>
			<div class="dropdown-divider"></div>
		<?php }
			} ?>
		</div>

  <?php } ?>

	<?php 
	$CI =& get_instance();
    $CI->load->model('B2BOrdersModel');
	/*echo "<pre>";
	print_r($OrderData);
	exit;*/
	
	if ($current_tab=='order' || $current_tab=='split-order') { ?>
	<?php if($OrderData->status == 0){ ?>
	<!-- <a class="purple-btn" type="button" href="<?php echo base_url(); ?>webshop/b2b/order/process/<?php echo $OrderData->order_id; ?>">Processing</a> -->
	 <button class="purple-btn blue-color" 
            type="button" 
            onclick="markAsProcessing('<?= $OrderData->order_id ?>')">
        Processing
    </button>
	<?php } ?>
	<?php if($OrderData->status == 7 && $OrderData->shipment_type == 1){ ?>
	
	   <button class="purple-btn bg-green" 
            type="button" 
            onclick="markAsCollected('<?= $OrderData->order_id ?>')">
        Mark As Collected
    </button>
	<?php } ?>
	<?php
	 $deliveryAttempts = $CI->B2BOrdersModel->getMultiDataById('b2b_orders_delivery_details', array('order_id' => $OrderData->order_id), '', 'id', 'ASC');
	 if (!empty($deliveryAttempts)) : 
    
        // Get the latest attempt (last row)
        $lastAttempt = end($deliveryAttempts);

     endif; 
	 ?>
		<?php if(($OrderData->status == 4 || $OrderData->status == 5 || $OrderData->status == 6 )  && $OrderData->shipment_type == 1  && $lastAttempt->delivery_status != 2 && $lastAttempt->delivery_status != 4){ ?>
	
	   <button class="purple-btn bg-green" 
            type="button" 
            onclick="markAsDelivered('<?= $OrderData->order_id ?>')">
        Mark As Delivered
    </button>
	<?php } ?>
	<?php if($OrderData->status == 1 && $OrderData->shipment_type == 1){ ?>
	<!-- <a class="purple-btn" type="button" href="<?php echo base_url(); ?>webshop/b2b/order/shipment/<?php echo $OrderData->order_id; ?>">Ship order</a> -->
	<button class="purple-btn blue-color" id="initiate-shipment-btn" onclick="InitiateShipment(<?php echo $OrderData->order_id; ?>,'<?php echo $OrderData->increment_id; ?>',<?php echo $OrderData->publisher_id; ?> );">Ship order</button>
	<?php } ?>

    <?php if($OrderData->status == 1 && $OrderData->shipment_type == 2){ ?>
   <button class="purple-btn blue-color" 
            type="button" 
            onclick="generatePickup('<?= $OrderData->order_id ?>')">
        Generate Pickup
    </button>
	<?php } ?>

	<!-- <a class="purple-btn" type="button" target="_blank" href="<?php echo base_url(); ?>b2b/order/print/<?php echo $OrderData->order_id; ?>">Print</a> -->
	<?php } elseif ($current_tab=='shipped-order') { ?>
	<!-- <a class="purple-btn" type="button" target="_blank" href="<?php echo base_url(); ?>b2b/shipped-order/print/<?php echo $OrderData->order_id; ?>">Print</a> -->
	<?php } ?>

	</div>
</div>
<script>
	
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
}

	function generatePickup(orderId) {
    swal({
        title: "Are you sure?",
        text: "Do you really want to generate pickup request?",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    },
    function(willCollect) {   // <-- v1 uses callback here
        if (willCollect) {
            $.ajax({
                url: BASE_URL + "B2BOrdersController/generatePickup",
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


	function markAsProcessing(orderId) {
    swal({
        title: "Are you sure?",
        text: "Do you really want to mark this order as processing?",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    },
    function(willCollect) {   // <-- v1 uses callback here
        if (willCollect) {
            $.ajax({
                url: BASE_URL + "B2BOrdersController/markProcessing",
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
function markAsCollected(orderId) {
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


/*function initiateshipmentform() {
    console.log("initiateshipmentform");

    $('#initiate_shipment_form').validate({
        ignore: [],
        rules: {
            delivery_person: { required: true }
        }
    });

    var form = $('#initiate_shipment_form');

    $.ajax({
        url: BASE_URL + "B2BOrdersController/InitiateShipment",
        type: "POST",
        dataType: 'json',
        data: form.serialize(),
        success: function(response) {
            $('#modal').modal('hide');
            console.log("Server response:", response);

            if (response.status == 200) {
                // SweetAlert v2
                if (typeof Swal !== "undefined") {
                    Swal.fire({
                        title: "Success",
                        text: response.message,
                        icon: "success",
                    }).then(() => {
                        console.log("Reload triggered!");
                        window.location.href = window.location.href; // reload page
                    });
                } else {
                    // SweetAlert v1 fallback
                    swal({
                        title: "Success",
                        text: response.message,
                        icon: "success",
                        buttons: true
                    }, function() {
                        console.log("Reload triggered (v1)!");
                        window.location.href = window.location.href; // reload page
                    });
                }
            } else {
                // Error alert
                if (typeof Swal !== "undefined") {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error"
                    });
                } else {
                    swal("Error", response.message, "error");
                }
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX error:", error);
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    title: "Error",
                    text: "Something went wrong.",
                    icon: "error",
                });
            } else {
                swal("Error", "Something went wrong.", "error");
            }
        }
    });
}*/

/*function initiateshipmentform() {
    console.log("initiateshipmentform");

    var form = $('#initiate_shipment_form');
    form.validate({
        ignore: [],
        rules: {
            delivery_person: { required: true }
        }
    });

    if (!form.valid()) {
        console.log("Form validation failed!");
        return;
    }

    $.ajax({
        url: BASE_URL + "B2BOrdersController/InitiateShipment",
        type: "POST",
        data: form.serialize(),
        success: function(response) {
            console.log("Raw response:", response);
            try {
                if (typeof response === 'string') response = JSON.parse(response);
            } catch (e) {
                console.error("Invalid JSON from server:", response);
                Swal.fire("Error", "Unexpected server response.", "error");
                return;
            }

            $('#modal').modal('hide');
            if (response.status == 200) {
                setTimeout(function() {
                    Swal.fire({
                        title: "Success",
                        text: response.message,
                        icon: "success"
                    }).then(() => window.location.reload(true));
                }, 300);
            } else {
                Swal.fire({
                    title: "Error",
                    text: response.message,
                    icon: "error"
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX error:", error, xhr.responseText);
            Swal.fire("Error", "Something went wrong.", "error");
        }
    });
}*/

function initiateshipmentform(e) {
    if (e) e.preventDefault(); // ✅ stop hard refresh
    console.log("Initiate shipment form triggered");

    var form = $('#initiate_shipment_form');

    // Optional: add validation
    form.validate({
        ignore: [],
        rules: {
            delivery_person: { required: true },
            delivery_date: { required: true }
        }
    });

    if (!form.valid()) {
        console.log("Form validation failed!");
        return false;
    }

    $.ajax({
        url: BASE_URL + "B2BOrdersController/InitiateShipment",
        type: "POST",
        data: form.serialize(),
        dataType: "json",
        success: function(response) {
            console.log("AJAX success:", response);

            if (response.status == 200) {
                // Close modal
                $('#FBCUserCommonModal').modal('hide');

                // Show success alert (SweetAlert v1 syntax)
                swal({
                    title: "Success",
                    text: response.message,
                    icon: "success",
                    buttons: true
                }, function() {
                    location.reload(true);
                });
            } else {
                swal("Error", response.message, "error");
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX error:", error);
            swal("Error", "Something went wrong. Please try again.", "error");
        }
    });

    return false; // ✅ prevent normal submission
}


		function InitiateShipment(order_id, inc_id, publisher_id) {
		// alert(order_id);
		// return false;
		if (order_id != '') {
			$.ajax({
				url: BASE_URL + "B2BOrdersController/InitiateShipmentPopup",
				type: "POST",
				data: {
					publisher_id: publisher_id,
					order_id: order_id,
					inc_id: inc_id
				},
				success: function(response) {
					if (response != 'error') {
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					} else {
						return false;
					}

				}
			});
		} else {
			return false;
		}

	}

    

</script>