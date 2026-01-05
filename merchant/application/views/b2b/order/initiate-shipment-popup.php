

<div class="modal-header">
    <h4 class="head-name">Assign delivery</h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>

<!-- <form id="initiate_shipment_form" name="initiate_shipment_form" method="post" onsubmit="initiateshipmentform()"> -->
    <form id="initiate_shipment_form" name="initiate_shipment_form" method="post" onsubmit="return initiateshipmentform(event)">

    
    <input type="hidden" name="hidden_order_id" value="<?php echo $order_id; ?>" />
    <input type="hidden" name="hidden_b2b_order_id" value="<?php echo $OrderData->increment_id; ?>" />
    <input type="hidden" name="hidden_publisher_id" value="<?php echo $OrderData->publisher_id; ?>" />

    <div class="modal-body">
        <div class="message-box-popup col-sm-12">
            <div class="row">
                <div class="col-sm-7 customize-add-inner-sec">

                    <div class="clear pad-bt-20"></div>

                    <!-- Delivery Date -->
                    <label for="delivery_date">Select Delivery Date <span class="required">*</span></label>
                    <input type="text" class="form-control" name="delivery_date" id="delivery_date" required placeholder="dd/mm/yyyy" />

                    <div class="clear pad-bt-20"></div>

                    <!-- Delivery Person -->
                  <label for="delivery_person">Enter Delivery Person <span class="required">*</span></label>
                  <input type="text" name="delivery_person" id="delivery_person" class="form-control" placeholder="Enter Delivery Person" required>


                    <div class="clear pad-bt-20"></div>

                    <!-- Remarks -->
                    <label for="remarks">Remarks</label>
                    <textarea class="form-control" name="remarks" id="remarks" placeholder="Remarks Area" maxlength="250"></textarea>
                </div>

                <div class="download-discard-small pos-ab-bottom">
                    <input type="submit" class="download-btn blue-color" name="InitiateFormsubmit" id="InitiateFormsubmit" value="Mark as Ship">
                </div>
            </div>
        </div>
    </div>
</form>
<script>
$(document).ready(function() {
    $('#delivery_date').datepicker({
        dateFormat: 'dd/mm/yy',  // dd/mm/yyyy format
        minDate: 0                // today onwards
    });
});
</script>