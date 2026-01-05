<?php $this->load->view('common/header'); ?>
<div class="container py-5">
  <div class="card shadow-sm p-4 mx-auto" style="max-width: 600px;">
    <h3 class="text-center mb-4"><?= $this->lang->line('payment_status') ?: 'Payment Status'; ?></h3>

    <?php if ($order['status'] === 'paid'): ?>
      <div class="alert alert-success text-center">
        ✅ <?= $this->lang->line('payment_success') ?: 'Payment Successful!'; ?>
      </div>
    <?php elseif ($order['status'] === 'failed'): ?>
      <div class="alert alert-danger text-center">
        ❌ <?= $this->lang->line('payment_failed') ?: 'Payment Failed!'; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-warning text-center">
        ⏳ <?= $this->lang->line('payment_pending') ?: 'Payment Pending or Unknown.'; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($order)): ?>
      <table class="table table-bordered mt-4">
        <?php if (!empty($order['order_id'])): ?>
          <tr><th>Order ID</th><td><?= htmlspecialchars($order['order_id']); ?></td></tr>
        <?php endif; ?>

        <?php if (!empty($order['amount'])): ?>
          <tr><th>Amount</th><td><?= htmlspecialchars($order['amount']); ?></td></tr>
        <?php endif; ?>

        <?php if (!empty($order['transaction_id'])): ?>
          <tr><th>Transaction ID</th><td><?= htmlspecialchars($order['transaction_id']); ?></td></tr>
        <?php endif; ?>

        <?php if (!empty($order['error_code'])): ?>
          <tr><th>Error Code</th><td><?= htmlspecialchars($order['error_code']); ?></td></tr>
        <?php endif; ?>

        <?php if (!empty($order['timestamp'])): ?>
          <tr><th>Timestamp</th><td><?= htmlspecialchars($order['timestamp']); ?></td></tr>
        <?php endif; ?>
      </table>
    <?php endif; ?>

    <div class="text-center mt-4">
      <a href="<?= site_url(); ?>" class="btn btn-primary">Return to Home</a>
    </div>
  </div>
</div>

<?php $this->load->view('common/footer'); ?>
