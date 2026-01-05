<?php $this->load->view('common/header'); ?>

<main class="container py-4">
  <h1>Purchase: <?= htmlspecialchars($value->title) ?></h1>

  <div class="card">
    <div class="card-body">
      <!-- Normal form submit instead of AJAX -->
      <form id="giftPurchaseForm" method="post" action="<?= site_url('Giftcards/processPurchase') ?>">
        <input type="hidden" name="value_id" value="<?= $value->id ?>">

        <div class="form-group mb-3">
          <label>Amount (MUR)</label>
          <input type="text" class="form-control" value="<?= number_format($value->amount,2) ?>" readonly>
        </div>

        <div class="form-group mb-3">
          <label>Receiver Name</label>
          <input type="text" name="receiver_name" class="form-control" required>
        </div>

        <div class="form-group mb-3">
          <label>Receiver Email</label>
          <input type="email" name="receiver_email" class="form-control" required>
        </div>

        <div class="form-group mb-3">
          <label>Greeting Message (optional)</label>
          <textarea name="message" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success w-100">Buy Gift Card</button>
      </form>
    </div>
  </div>
</main>

<?php $this->load->view('common/footer'); ?>
