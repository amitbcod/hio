<?php $this->load->view('common/header'); ?>

<div class="breadcrum-section">
    <div class="container">
        <div class="breadcrum">
            <ul class="breadcrumb">
                <li><a href="<?= base_url(); ?>">Home</a></li>
                <li class="active">Messaging</li>
            </ul>
        </div>
    </div>
</div>

<div class="my-profile-page-full">
    <div class="container">
        <div class="row">
            <?php $this->load->view('common/profile_sidebar'); ?>

            <div class="col-sm-9 col-md-9">
                <?php if (!empty($messaging_data)) : ?>
                    <div class="card mb-4">
                        <!-- <div class="card-header">
                            <strong>Conversation for Product: <?= $messaging_data[0]->product_id; ?></strong>
                        </div> -->
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <?php foreach ($messaging_data as $msg) : ?>
                                    <?php if (!empty($msg->message)) : ?>
                                        <li class="mb-3 text-start">
                                            <div class="bg-light p-2 rounded">
                                                <strong><?= htmlspecialchars($msg->name); ?> (You)</strong>
                                                <small class="text-muted">
                                                    (<?= date('d M Y, H:i', strtotime($msg->created_at)); ?>)
                                                </small>
                                                <p><?= nl2br(htmlspecialchars($msg->message)); ?></p>
                                            </div>
                                        </li>
                                    <?php endif; ?>

                                    <?php if (!empty($msg->merchant_reply)) : ?>
                                        <li class="mb-3 text-start">
                                            <div class="bg-primary text-white p-2 rounded">
                                                <strong>Merchant</strong>
                                                <small class="text-white">
                                                    (<?= date('d M Y, H:i', strtotime($msg->updated_at)); ?>)
                                                </small>
                                                <p><?= nl2br(htmlspecialchars($msg->merchant_reply)); ?></p>
                                            </div>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>

                            <!-- New Query Form -->
                            <form method="POST" id="new-customer-query-form" action="<?= base_url('MyProfileController/messagingPost'); ?>" class="mt-3">
                                <input type="hidden" name="name" value="<?= $messaging_data[0]->name; ?>">
                                <input type="hidden" name="category" value="<?= $messaging_data[0]->category; ?>">
                                <input type="hidden" name="email" value="<?= $messaging_data[0]->email; ?>">
                                <input type="hidden" name="message" value="<?= $messaging_data[0]->message; ?>">
                                <input type="hidden" name="product_id" value="<?= $messaging_data[0]->product_id; ?>">
                                <input type="hidden" name="merchant_id" value="<?= $messaging_data[0]->merchant_id; ?>">
                                <input type="hidden" name="customer_id" value="<?= $_SESSION['LoginID']; ?>">

                                <div class="form-group">
                                    <label>New Query</label>
                                    <textarea name="message" class="form-control" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Send Query</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No messages found for this product.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('common/footer'); ?>
<!-- jQuery (already in your project) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ✅ Add SweetAlert2 library -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Your custom script -->
<script>
$(document).ready(function() {
    $('#new-customer-query-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#new-customer-query-form button[type="submit"]').prop('disabled', true).text('Submitting...');
            },
            success: function(response) {
                if (response.flag == 1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.msg,
                        timer: 1000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', response.msg, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Something went wrong!', 'error');
            },
            complete: function() {
                $('#new-customer-query-form button[type="submit"]').prop('disabled', false).text('Send Query');
            }
        });
    });
});
</script>
