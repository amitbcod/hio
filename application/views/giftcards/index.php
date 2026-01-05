<?php $this->load->view('common/header'); ?>
<main class="container py-4">
  <h1>Buy Gift Card</h1>
  <div class="row">
    <?php foreach ($values as $v): ?>
      <div class="col-md-4 giftcard">
        <div class="card mb-3">
          <div class="card-body text-center">
            <h5><?= htmlspecialchars($v->title) ?></h5>
            <p><strong>Amount: MUR</strong> <?= number_format($v->amount,2) ?></p>
            <a href="<?= site_url('Giftcards/purchase/'.$v->id) ?>" class="btn btn-primary">Buy</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</main>
<?php $this->load->view('common/footer'); ?>
