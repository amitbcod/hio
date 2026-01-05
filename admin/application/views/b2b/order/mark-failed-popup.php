<div class="modal-header">
    <h4 class="head-name">Mark Delivery as Failed</h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>

<form id="mark_failed_form">
    <div class="form-group">
        <label>Select Reason for Marking Failed</label>
        <select name="reason_for_attempt_failed" class="form-control" required>
            <option value="">-- Select Reason --</option>
            <option value="Nobody Answering Call">Nobody Answering Call</option>
            <option value="Nobody At Home">Nobody At Home</option>
            <option value="Danger Condition">Danger Condition e.g. Dogs</option>
            <option value="Weather Condition">Weather Condition e.g. Flooding</option>
            <option value="Customer Requested To Reschedule">Customer Requested To Reschedule</option>
        </select>
    </div>

    <input type="hidden" name="order_id" value="<?= $order_id ?>">
    <input type="hidden" name="attempt_no" value="<?= $attempt_no ?>">


    <div class="clear pad-bt-20"></div>

    <button type="button" class="btn btn-danger" onclick="submitMarkFailedForm()">Mark as Failed</button>
</form>

<!-- <script>
function submitMarkFailedForm() {
    var form = $('#mark_failed_form');
    $.ajax({
        url: BASE_URL + "B2BOrdersController/MarkAsFailed",
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
                location.reload();
            });
        }
    });
    return false;
}
</script> -->
