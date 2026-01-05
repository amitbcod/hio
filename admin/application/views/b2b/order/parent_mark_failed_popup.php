<div class="modal-header">
    <h4 class="head-name">Mark Parent Delivery as Failed</h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>

<form id="mark_parent_failed_form">
    <div class="modal-body">
        <div class="form-group">
            <label>Select Reason for Marking Failed</label>
            <select name="reason_for_attempt_failed" class="form-control" required>
                <option value="">-- Select Reason --</option>
                <option value="Nobody Answering Call">Nobody Answering Call</option>
                <option value="Nobody At Home">Nobody At Home</option>
                <option value="Danger Condition">Danger Condition (Dogs, etc.)</option>
                <option value="Weather Condition">Weather Condition (Flooding, etc.)</option>
                <option value="Customer Requested To Reschedule">Customer Requested To Reschedule</option>
            </select>
        </div>

        <input type="hidden" name="webshop_order_id" value="<?= htmlspecialchars($webshop_order_id) ?>">
        <input type="hidden" name="attempt_no" value="<?= htmlspecialchars($attempt_no) ?>">
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger" onclick="submitMarkParentFailedForm()">Mark as Failed</button>
    </div>
</form>

<script>
function submitMarkParentFailedForm() {
    var form = $('#mark_parent_failed_form');

    // Check if reason is selected
    if (!$('select[name="reason_for_attempt_failed"]').val()) {
        Swal.fire("Error", "Please select a reason for marking failed.", "error");
        return false;
    }

    $.ajax({
        url: BASE_URL + "B2BOrdersController/markParentFailed",
        type: "POST",
        data: form.serialize(),
        dataType: "json", // ✅ Expect JSON
        success: function(res) {
            $('#FBCUserCommonModal').modal('hide');

            Swal.fire({
                title: res.status == 200 ? "Success" : "Error",
                text: res.message,
                icon: res.status == 200 ? "success" : "error",
                confirmButtonText: 'OK'
            }).then(() => {
                if (res.status == 200) location.reload();
            });
        },
        error: function() {
            Swal.fire("Error", "Something went wrong. Please try again.", "error");
        }
    });

    return false;
}
</script>
