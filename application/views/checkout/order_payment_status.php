<?php $this->load->view('common/header'); ?>
<div class="container py-5">
  <div class="card shadow-sm p-4 mx-auto" style="max-width: 600px;">
    <h3 class="text-center mb-4"><?= $PageTitle ?? $this->lang->line('payment_status'); ?></h3>

    <?php if ($status === 'success'): ?>
      <div class="alert alert-success text-center">
        ✅ <?= $this->lang->line('payment_success'); ?>
      </div>
    <?php elseif ($status === 'failed'): ?>
      <div class="alert alert-danger text-center">
        ❌ <?= $this->lang->line('payment_failed'); ?>
      </div>
    <?php else: ?>
      <div class="alert alert-warning text-center">
        ⏳ <?= $this->lang->line('payment_pending'); ?>
      </div>
    <?php endif; ?>

    <table class="table table-bordered mt-4">
      <?php if (!empty($order_id)): ?>
        <tr><th><?= $this->lang->line('order_id'); ?></th><td><?= htmlspecialchars($order_id); ?></td></tr>
      <?php endif; ?>

      <?php if (!empty($increment_id)): ?>
        <tr><th><?= $this->lang->line('increment_id'); ?></th><td><?= htmlspecialchars($increment_id); ?></td></tr>
      <?php endif; ?>

      <?php if (!empty($amount)): ?>
        <tr><th><?= $this->lang->line('amount'); ?></th><td><?= htmlspecialchars($amount); ?> <?= htmlspecialchars($currency ?? ''); ?></td></tr>
      <?php endif; ?>

      <?php if (!empty($payment_method)): ?>
        <tr><th><?= $this->lang->line('payment_method'); ?></th><td><?= htmlspecialchars($payment_method); ?></td></tr>
      <?php endif; ?>
    </table>

    <div class="text-center mt-4">
      <a href="<?= site_url(); ?>" class="btn btn-primary"><?= $this->lang->line('return_home'); ?></a>
    </div>
  </div>
</div>

<?php $this->load->view('common/footer'); ?>
