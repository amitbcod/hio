<?php $this->load->view('common/header'); ?>
<main class="container py-4">
  <h1>Payment - Gift Card</h1>
  <p>Order #: <?= htmlspecialchars($order->order_number) ?></p>
  <p>Amount: <?= number_format($order->amount,2) ?></p>
  <p>Receiver: <?= htmlspecialchars($order->receiver_name) ?> (<?= htmlspecialchars($order->receiver_email) ?>)</p>

  <p class="text-warning">This is a test payment page. In production replace this controller with your gateway integration.</p>

  <form method="post" action="<?= site_url('PaymentGateway/giftcardCallback') ?>">
    <input type="hidden" name="order_id" value="<?= $order->id ?>">
    <button type="submit" name="action" value="success" class="btn btn-success">Simulate Success</button>
    <button type="submit" name="action" value="fail" class="btn btn-danger">Simulate Fail</button>
  </form>
</main>
<?php $this->load->view('common/footer'); ?>
