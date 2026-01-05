<?php $this->load->view('common/header'); ?>

<main class="container py-4">
    <div class="card">
        <div class="card-body text-center">
            <h1 class="text-danger mb-3">Payment Failed</h1>
            <p class="mb-3">
                Unfortunately, your gift card purchase could not be completed at this time.
            </p>

            <?php if (!empty($order_number)): ?>
                <p>Order Number: <strong><?= htmlspecialchars($order_number) ?></strong></p>
            <?php endif; ?>

            <p>Please try again or contact our support team if the problem persists.</p>

            <a href="<?= site_url('Giftcards') ?>" class="btn btn-primary mt-3">Back to Gift Cards</a>
        </div>
    </div>
</main>

<?php $this->load->view('common/footer'); ?>
