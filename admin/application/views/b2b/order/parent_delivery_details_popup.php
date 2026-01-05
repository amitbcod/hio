<div class="modal-header">
  <h5 class="modal-title">Parent-Level Delivery Details</h5>
  <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
</div>

<div class="modal-body">
  <?php if (!empty($attempts)): ?>
    <div style="max-height:400px; overflow-y:auto;">
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Attempt #</th>
          <th>Delivery Type</th>
          <th>Driver / Person</th>
          <th>Status</th>
          <th>Date</th>
          <th>Remarks</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($attempts as $a): ?>
          <tr>
            <td><?= $a->delivery_attempt_no ?></td>
            <td><?= ($a->delivery_type == 1) ? 'Own' : 'YM' ?></td>
            <td><?= ($a->delivery_type == 1) ? $a->own_delivery_person_name : $this->CommonModel->getDriverName($a->driver_id) ?></td>
            <td><?= $this->CommonModel->getDeliveryStatusLabel($a->delivery_status) ?></td>
            <td><?= date('d-M-Y H:i', strtotime($a->delivery_date)) ?></td>
            <td><?= $a->remarks ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php else: ?>
    <p class="text-center">No delivery attempts found.</p>
  <?php endif; ?>
</div>
