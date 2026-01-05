<?php $this->load->view('common/header'); ?>
<main class="container py-4">
  <h1>Gift Card Purchase Successful</h1>

  <p>Order #: <?= htmlspecialchars($order->order_number) ?></p>
  <p>Amount: <?= number_format($order->amount,2) ?></p>
  <p>Receiver: <?= htmlspecialchars($order->receiver_name) ?> (<?= htmlspecialchars($order->receiver_email) ?>)</p>

  <?php if (!empty($gift_card)): ?>
    <div class="alert alert-success">
      <h4>Gift Card Issued</h4>
      <p><strong>Code:</strong> <?= htmlspecialchars($gift_card->code) ?></p>
      <p><strong>Balance:</strong> <?= number_format($gift_card->balance,2) ?></p>
    </div>
  <?php else: ?>
    <p class="text-info">Gift card is not yet issued. If you just paid, refresh after a few seconds.</p>
  <?php endif; ?>

</main>
<?php $this->load->view('common/footer'); ?>
