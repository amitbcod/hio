<?php 

$this->load->view('common/fbc-user/header'); ?>

<div class="container py-5 text-center">
  <?php if ($transaction['status'] == 'success'): ?>
    <h3 class="text-success">✅ Payment Successful</h3>
    <p>Your addon has been activated.</p>
  <?php elseif ($transaction['status'] == 'failed'): ?>
    <h3 class="text-danger">❌ Payment Failed</h3>
    <p>Unfortunately, your payment could not be completed.</p>
  <?php else: ?>
    <h3 class="text-warning">⏳ Payment Pending</h3>
    <p>Please wait while we confirm your payment.</p>
  <?php endif; ?>

  <hr>
  <h5>Transaction Reference:</h5>
  <p><?= htmlspecialchars($transaction['transaction_ref'] ?? '-') ?></p>
  <p><a href="<?= base_url('addons'); ?>" class="btn btn-primary mt-3">Back to Addons</a></p>
</div>
<?php $this->load->view('common/fbc-user/footer'); ?>