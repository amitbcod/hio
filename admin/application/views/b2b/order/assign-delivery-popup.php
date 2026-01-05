<div class="modal-header">
    <h4 class="head-name">Assign New Delivery Attempt</h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>

<form id="assign_delivery_form">

   <div class="form-group">
    <label>Select Delivery Person</label>
    <select name="driver_id" id="driver_id" class="form-control" required>
        <option value="">-- Select Delivery Person --</option>
        <?php if (!empty($delivery_persons)): ?>
            <?php foreach ($delivery_persons as $person): ?>
                <option value="<?= $person->id ?>">
                    <?= htmlspecialchars($person->first_name . ' ' . $person->last_name) ?>
                    (<?= htmlspecialchars($person->mobile_no) ?>)
                </option>
            <?php endforeach; ?>
        <?php else: ?>
            <option value="">No delivery persons available</option>
        <?php endif; ?>
    </select>
</div>

    <div class="form-group">
        <label>Delivery Date</label>
        <input type="date" name="delivery_date" id="delivery_date" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="remarks">Delivery Remarks</label>
        <textarea class="form-control" name="remarks" id="remarks" placeholder="Remarks for delivery person" maxlength="250"></textarea>
    </div>

    <!-- hidden fields -->
    <input type="hidden" name="order_id" value="<?= $order_id ?>">
    <input type="hidden" name="attempt_no" value="<?= $attempt_no ?>">

    <div class="clear pad-bt-20"></div>

    <button type="button" class="btn btn-success blue-color" onclick="submitAssignDeliveryForm()">Assign Delivery</button>
</form>

<script>

function submitAssignDeliveryForm() {
    var form = $('#assign_delivery_form');
    var btn = form.find('button[type="button"]');
    btn.prop('disabled', true);

    $.ajax({
        url: BASE_URL + "B2BOrdersController/AssignNewDelivery",
        type: "POST",
        data: form.serialize(),
        dataType: "json", // ensures response is parsed automatically
        success: function(res) {
            btn.prop('disabled', false);
            $('#FBCUserCommonModal').modal('hide');

            Swal.fire({
                title: res.status == 200 ? "Success" : "Error",
                text: res.message,
                icon: res.status == 200 ? "success" : "error",
                confirmButtonText: "OK"
            }).then(() => {
                if (res.status == 200) location.reload();
            });
        },
        error: function(xhr, status, error) {
            btn.prop('disabled', false);
            Swal.fire({
                title: "Error",
                text: "Something went wrong. Please try again.",
                icon: "error",
                confirmButtonText: "OK"
            });
            console.error(xhr.responseText);
        }
    });

    return false;
}

</script>
