<?php $this->load->view('common/fbc-user/header'); ?>

<main class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <?php
    $chat_user = 'Customer';
    if (!empty($product_questions_data)) {
        foreach ($product_questions_data as $header_msg) { // rename variable
            if (!empty($header_msg->customer_id) && $header_msg->customer_id > 0 && !empty($header_msg->name)) {
                $chat_user = htmlspecialchars(trim(preg_replace('/[()]+$/', '', $header_msg->name)));
                break;
            } elseif (empty($header_msg->customer_id) || $header_msg->customer_id == 0) {
                $chat_user = htmlspecialchars($header_msg->email);
                break;
            }
        }
    }
    ?>
    <h3>Chat with <?= $chat_user; ?></h3>

    <div class="chat-box" style="max-height:500px; overflow-y:auto; border:1px solid #ccc; padding:15px; margin-bottom:20px;">
        <?php if (!empty($product_questions_data)): ?>
            <?php foreach ($product_questions_data as $msg): ?>

                <?php if (!empty($msg->message)): ?>
                    <div class="customer-message mb-2" style="text-align:left;">
                        <strong><?= htmlspecialchars($msg->name ?: $msg->email); ?> (Customer)</strong>
                        <small class="text-muted">(<?= date('d M Y, H:i:s', strtotime($msg->created_at)); ?>)</small>
                        <p style="background:#f1f1f1; display:inline-block; padding:10px; border-radius:10px;">
                            <?= nl2br(htmlspecialchars($msg->message)); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($msg->merchant_reply)): ?>
                    <div class="merchant-message mb-2" style="text-align:right;">
                        <strong>Merchant (You)</strong>
                        <small class="text-muted">(<?= date('d M Y, H:i:s', strtotime($msg->created_at)); ?>)</small>
                        <p style="background:#007bff; color:white; display:inline-block; padding:10px; border-radius:10px;">
                            <?= nl2br(htmlspecialchars($msg->merchant_reply)); ?>
                        </p>
                    </div>
                <?php endif; ?>

            <?php endforeach; ?>
        <?php else: ?>
            <p>No messages yet.</p>
        <?php endif; ?>
    </div>


    <!-- Merchant Reply Form -->
    <form method="POST" action="<?= base_url('Mydocuments/update_messaging'); ?>">
        <input type="hidden" name="product_id" value="<?= $product_id; ?>">
        <input type="hidden" name="customer_identifier" value="<?= $customer_identifier; ?>">

        <div class="form-group">
            <label>Reply</label>
            <textarea name="merchant_reply" class="form-control" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Send Reply</button>
    </form>

</main>

<?php $this->load->view('common/fbc-user/footer'); ?>