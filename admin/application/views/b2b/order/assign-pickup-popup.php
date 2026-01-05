<div class="modal-header">
    <h4 class="head-name">Assign New Pickup</h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>

<form id="assign_pickup_form">

    <div class="form-group">
    <label>Select Pickup Person</label>
    <select name="driver_id" id="driver_id" class="form-control" required>
        <option value="">-- Select Pickup Person --</option>
        <?php if (!empty($delivery_persons)): ?>
            <?php foreach ($delivery_persons as $person): ?>
                <option value="<?= $person->id ?>">
                    <?= htmlspecialchars($person->first_name . ' ' . $person->last_name) ?>
                    (<?= htmlspecialchars($person->mobile_no) ?>)
                </option>
            <?php endforeach; ?>
        <?php else: ?>
            <option value="">No pickup persons available</option>
        <?php endif; ?>
    </select>
</div>

    <div class="form-group">
        <label for="remarks">Pickup Remarks</label>
        <textarea class="form-control" name="remarks" id="remarks" placeholder="Remarks for pickup person" maxlength="250"></textarea>
    </div>

    <!-- hidden field -->
    <input type="hidden" name="order_id" value="<?= $order_id ?>">

    <div class="clear pad-bt-20"></div>

    <button type="button" class="btn btn-success blue-color" onclick="submitAssignPickupForm()">Assign Pickup</button>
</form>

<script>
function submitAssignPickupForm() {
    var form = $('#assign_pickup_form');
    $.ajax({
        url: BASE_URL + "B2BOrdersController/AssignNewPickup",
        type: "POST",
        data: form.serialize(),
        success: function(response) {
            $('#FBCUserCommonModal').modal('hide');
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
    return false;
}
</script>
