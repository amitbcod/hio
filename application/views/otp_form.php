<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- OTP Modal -->
    <!-- <?php
        // $MediaPath = IMAGE_URL_SHOW . '/products/large/';
    ?> -->
   <!-- <div style="background-image: url('<?php echo $MediaPath . $base_image; ?>'); background-size: cover; background-position: center; min-height: 100vh;">
</div> -->

<!-- OTP Modal (outside background div) -->
<div id="otpModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enter OTP</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="<?= base_url('emagazine/validateOtp') ?>?encoded_id=<?= urlencode($encoded_id) ?>">
                    <input type="hidden" name="encoded_id" value="<?= $encoded_id ?>">
                    <input type="hidden" name="product_id" value="<?= $product_id ?>">
                    <input type="text" name="otp" class="form-control mb-2" placeholder="Enter OTP" required>
                    <button type="submit" class="btn btn-primary btn-block">Submit</button>
                </form>
                <?php if (isset($error)): ?>
                    <div style="color: red;"><?= $error ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

    <!-- Include jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Show the OTP modal on page load
            $('#otpModal').modal('show');
        });
    </script>
</body>
</html>
