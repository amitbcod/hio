<div class="sigin-form login-eye">
    <div class="form-group form-group <?php echo ($display_page == 'checkout') ? 'first' : '' ?> emailmobile">
        <input class="form-control" type="email" name="emailmobile" id="emailmobile" 
               placeholder="<?= $this->lang->line('email'); ?>">
    </div><!-- form-box -->

    <div class="form-group <?php echo ($display_page == 'checkout') ? 'second' : '' ?> password d-none">
        <input class="form-control" type="password" id="login_password" name="login_password" 
               placeholder="<?= $this->lang->line('password'); ?>">
    </div>

    <div class="form-group <?php echo ($display_page == 'checkout') ? 'second' : '' ?> d-none otp_verification">
        <input class="form-control" type="number" 
               id="<?php echo ($display_page == 'checkout') ? 'reg_password' : 'conf_password' ?>" 
               name="otp_verification" 
               placeholder="<?= $this->lang->line('enter_otp'); ?>">
        <p class="login-otp"></p>
    </div><!-- form-box -->

    <p class="forgot-password">
        <a href="<?php echo BASE_URL ?>customer/forgot-password"><?= $this->lang->line('forgot_password'); ?></a>
    </p>

    <input type="hidden" name="g-recaptcha-response" 
           id="<?php echo ($display_page == 'checkout') ? 'g-recaptcha-response-login' : 'g-recaptcha-response' ?>" 
           class="<?php echo ($display_page == 'checkout') ? 'g-recaptcha-response' : '' ?>">

    <?php if (isset($GLOBALS['captcha_check_flag_g']) && $GLOBALS['captcha_check_flag_g'] == 'no') { ?>
        <div class="col-sm-12">
            <input type="text" name="nickname" id="nickname" value="" style="display: none;">
        </div>
    <?php }  ?>

    <div class="signin-btn">
        <?php if ($display_page == 'login') { ?>
            <input type="button" class="btn btn-primary continue-btn" 
                   onclick="customerLoginOtpEmail();" 
                   name="signin-btn" id="signin-continue-btn" 
                   value="<?= $this->lang->line('continue'); ?>">
            <input type="submit" class="btn btn-primary btn-blue d-none" 
                   name="signin-btn" id="signin-btn" 
                   value="<?= $this->lang->line('login'); ?>">
        <?php } ?>
        <?php if ($display_page == 'checkout') { ?>
            <input type="button" class="btn btn-primary continue-btn" 
                   onclick="customerLoginOtpEmailCheckout();" 
                   name="signin-btn" id="signin-continue-btn" 
                   value="<?= $this->lang->line('continue'); ?>">
            <input class="btn btn-primary btn-blue d-none" type="submit" 
                   name="signin-btn" id="signin-btn" 
                   value="<?= $this->lang->line('login_continue'); ?>">
        <?php } ?>
    </div><!-- signin-btn -->
</div><!-- sigin-form -->
<script type="text/javascript">

</script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<!-- Load Bootstrap (if used) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="/path/to/your-script.js"></script>
<script>
    // $(document).ready(function () {
    // // Toggle password visibility
    //     $(document).on('click', '.toggle-password', function() {
    //         var input = $(this).siblings('input'); // Get the input field related to this toggle button

    //         // Toggle the type attribute of the input
    //         if (input.attr('type') === 'password') {
    //             input.attr('type', 'text');
    //             $(this).removeClass('eye-password').addClass('eye-text'); // Change icon to "eye-text"
    //         } else {
    //             input.attr('type', 'password');
    //             $(this).removeClass('eye-text').addClass('eye-password'); // Change icon to "eye-password"
    //         }
    //     });
    // });

</script>
<script type="text/javascript">
    // Checkout Page
    function customerLoginOtpEmailCheckout() {
        var emailmobile = $('#emailmobile').val();
        $.ajax({
            url: BASE_URL + "CheckoutController/customerLoginOtpEmail",
            type: "POST",
            data: {
                emailmobile: emailmobile
            },
            success: function(response) {
                var obj = JSON.parse(response);
                if (obj.flag == 1) {
                    $('.emailmobile').addClass('d-none');
                    $('.password').removeClass('d-none');
                    $('.continue-btn').addClass('d-none');
                    $('.btn-blue').removeClass('d-none');
                    $('.login-otp').html('Login OTP is :' + obj.data);
                } else if (obj.flag == 2) {
                    $('.emailmobile').addClass('d-none');
                    $('.otp_verification').removeClass('d-none');
                    $('.continue-btn').addClass('d-none');
                    $('.btn-blue').removeClass('d-none');
                    $('.login-otp').html('OTP is : ' + obj.data);
                } else {
                    swal({
                        title: "",
                        icon: "error",
                        text: obj.msg,
                        //buttons: false,
                        showCancelButton: true,
                        cancelButtonText: "CANCEL",
                    });
                }

            }
        });
    }



    function customerLoginOtpEmail() {
        var emailmobile = $('#emailmobile').val();
        $.ajax({
            url: BASE_URL + "CustomerController/customerLoginOtpEmail",
            type: "POST",
            data: {
                emailmobile: emailmobile
            },
            success: function(response) {
                var obj = JSON.parse(response);
                if (obj.flag == 1) {
                    $('.emailmobile').addClass('d-none');
                    $('.password').removeClass('d-none');
                    $('.continue-btn').addClass('d-none');
                    $('.btn-blue').removeClass('d-none');
                    $('.forgot-password').removeClass('d-none');

                } else if (obj.flag == 2) {
                    $('.emailmobile').addClass('d-none');
                    $('.otp_verification').removeClass('d-none');
                    $('.continue-btn').addClass('d-none');
                    $('.btn-blue').removeClass('d-none');
                    $('.login-otp').html('OTP is : ' + obj.data);

                } else {
                    swal({
                        title: "",
                        icon: "error",
                        text: obj.msg,
                        buttons: false,
                    });
                }

            }
        });
    }
</script>