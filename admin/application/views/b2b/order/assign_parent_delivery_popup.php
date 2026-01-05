<div class="modal-header">
  <h5 class="modal-title">Assign Parent-Level Delivery</h5>
  <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
</div>

<form id="parentDeliveryForm">
  <div class="modal-body">
    <input type="hidden" name="webshop_order_id" value="<?= htmlspecialchars($webshop_order_id) ?>">
    <input type="hidden" name="attempt_no" value="<?= htmlspecialchars($attempt_no) ?>">
    <input type="hidden" name="delivery_type" value="2"> <!-- Default YM Delivery -->

    <div class="form-group">
      <label>Select YM Driver</label>
      <select name="driver_id" class="form-control" required>
        <option value="">-- Select Driver --</option>
        <?php if (!empty($delivery_persons)) : ?>
          <?php foreach ($delivery_persons as $d) : ?>
            <option value="<?= htmlspecialchars($d->id) ?>">
              <?= htmlspecialchars($d->first_name . ' ' . $d->last_name) ?> (<?= htmlspecialchars($d->mobile_no) ?>)
            </option>
          <?php endforeach; ?>
        <?php else : ?>
          <option value="">No Drivers Found</option>
        <?php endif; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Delivery Date</label>
      <input type="date" name="delivery_date" class="form-control" required>
    </div>

    <div class="form-group">
      <label>Remarks</label>
      <textarea name="remarks" class="form-control" rows="2"></textarea>
    </div>
  </div>

  <div class="modal-footer">
    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-success btn-primary bg-blue">Save</button>
  </div>
</form>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function(){
  $('#parentDeliveryForm').on('submit', function(e){
    e.preventDefault();
    const form = $(this);

    $.post(BASE_URL + 'B2BOrdersController/saveParentDeliveryAssignment', form.serialize())
      .done(function(res){
        let data;
        try {
          data = typeof res === "object" ? res : JSON.parse(res);
        } catch(err){
          Swal.fire("Error", "Unexpected error occurred. Please try again.", "error");
          return;
        }

        Swal.fire({
          title: data.status === 200 ? "Success" : "Error",
          text: data.msg || data.message || "",
          icon: data.status === 200 ? "success" : "error",
          timer: 2000,
          timerProgressBar: true,
          showConfirmButton: false
        }).then(() => {
          if(data.status === 200) location.reload();
        });

      })
      .fail(function(){
        Swal.fire("Error", "Unable to contact server. Please try again.", "error");
      });
  });
});
</script>
