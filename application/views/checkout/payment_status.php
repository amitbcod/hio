<?php $this->load->view('common/header'); ?>

<div class="main">
    <div class="container">
        <ul class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>">home<?= lang('home') ?></a></li>
            <li class="">checkout<?= lang('checkout') ?></li>
            <li class="active">
                <?= $status == 'success' ? 'order placed' . lang('order_placed') : 'order failed' . lang('order_failed') ?>
            </li>
        </ul>

        <?php
        $divHide = $status == 'success' ? "" : "hide";
        $divHideFailed = $status == 'failed' ? "" : "hide";
        $msgImg = $status == 'success' ? 'thankyou-check.png' : 'failure.png';
        ?>

        <div class="row margin-bottom-40">
            <div class="col-md-12">
                <div class="content-page shadow">
                    <div class="PymtGtwMsgContainer">
                        <!-- Success image -->
                        <img id="thankyouImg" src="<?php echo SKIN_URL; ?>images/<?= $msgImg ?>" 
                             alt="<?= $status == 'success' ? 'Thank You' : 'Failed' ?>" 
                             class="img-responsive <?= $divHide ?> thankyou-check">

                        <!-- Failed image (optional, we already use $msgImg) -->
                        <h2 class="cod <?= $divHideCod ?? '' ?>"><?= lang('cod_verification_required') ?></h2>

                        <!-- Success message -->
                        <h3 class="thanky <?= $divHide ?>">Thank you for your Order!</h3>
                        <h4 class="thankyou-order-msg <?= $divHide ?>">Your order has been successfully placed.</h4>
                        <p class="ordnum thankyou-order-msg <?= $divHide ?>">Order Number: <?= $increment_id ? '#' . $increment_id : '' ?></p>
                        <p class="thankyou-email-msg <?= $divHide ?>">Tracking details will be sent to your registered email id, after your order has been dispatched.</p>

                        <!-- Failed message -->
                        <h3 class="thanky <?= $divHideFailed ?>">Something Went Wrong. Please Try Again.</h3>

                        <a class="btn btn-primary" role="button" href="<?php echo base_url(); ?>">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- COD OTP Section -->
        <?php if ($payment_method == 'cod' && $shop_flag == 2 && $status == 'success') { ?>
        <div class="row margin-bottom-40">
            <div class="col-md-12">
                <div class="content-page shadow">
                    <div class="otp-div">
                        <div class="opt-password" id="opt-passw">
                            <p><?= lang('since_you_have_placed_a_cod_order') ?></p>
                            <p class="cod-verification">
                                <span><?= lang('enter_cod_verification_otp') ?></span>
                                <input class="form-control" type="text" name="otp_password" id="otp_password" placeholder="<?= lang('enter_your_otp') ?>">
                            </p>
                            <div class="error-red" id="error-msg"></div>
                            <div class="verification-button">
                                <input id="valid-otp-btn" class="btn btn-blue" type="button" value="<?= lang('submit') ?>" onclick="return ValidOTP();">
                                <input id="regenerate-otp-btn" class="btn btn-black" type="button" value="<?= lang('regenerate_otp') ?>" onclick="OTP_regenerate();">
                            </div>
                        </div>

                        <div class="confirm-green" id="success-msg"></div>
                        <input type="hidden" name="order_id" id="order_id" value="<?= $order_id ?? '' ?>">
                        <input type="hidden" name="phone_no" id="phone_no" value="<?= $billingMobile ?? '' ?>">
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

    </div><!-- .container ends -->
</div><!-- .main ends -->

<?php $this->load->view('common/footer'); ?>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>checkout.js"></script>
</body>
</html>
