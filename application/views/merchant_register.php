<?php $this->load->view('common/header'); ?>
<div class="section sign-page">
	<div class="container">
	<div class="row">
          <div class="col-md-6 col-md-offset-3">
            <div class="content-page shadow margbot20 p-3">
              <ul class="nav nav-tabs" role="tablist">
                  <li role="presentation" class=""><a href="#login" aria-controls="login" role="tab" data-toggle="tab" aria-expanded="true">Login</a></li>
                  <li role="presentation" class="active"><a href="#register" aria-controls="register" role="tab" data-toggle="tab" aria-expanded="false">Register</a></li>
              </ul>
			  <div class="tab-content">
					<div role="tabpanel" class="tab-pane fade " id="login">
						<h4>Existing merchants, log in with your email</h4>
						<form id="login-user" method="POST" action="<?php echo BASE_URL; ?>merchant/UserController/loginPost" style="margin-top:10px;">
							<div class="sigin-form login-eye">
								<div class="form-group form-group <?php echo ($display_page == 'checkout') ? 'first' : '' ?> inputEmail">
									<input class="form-control" type="email" name="inputEmail" id="inputEmail" placeholder="Email">
								</div><!-- form-box -->

								<div class="form-group <?php echo ($display_page == 'checkout') ? 'second' : '' ?> password d-none">
									<input class="form-control" type="password" id="inputPassword" name="inputPassword" placeholder="Password">
									<!-- <span class="eye-password toggle-password"></span> -->
								</div>
								<div class="form-group <?php echo ($display_page == 'checkout') ? 'second' : '' ?> d-none otp_verification">
									<input class="form-control" type="number" id="<?php echo ($display_page == 'checkout') ? 'reg_password' : 'conf_password' ?>" name="otp_verification" id="otp_verification" placeholder="Enter Otp">
									<p class="login-otp"></p>
								</div><!-- form-box -->
								<!-- <p class="forgot-password"><a href="<?php echo BASE_URL ?>customer/forgot-password">Forgot Password</a></p> -->
								<!-- Google reCAPTCHA box -->
								<input type="hidden" name="g-recaptcha-response" id="<?php echo ($display_page == 'checkout') ? 'g-recaptcha-response-login' : 'g-recaptcha-response' ?>" class="<?php echo ($display_page == 'checkout') ? 'g-recaptcha-response' : '' ?>">

								<?php if (isset($GLOBALS['captcha_check_flag_g']) && $GLOBALS['captcha_check_flag_g'] == 'no') { ?>
									<div class="col-sm-12">
										<input type="text" name="nickname" id="nickname" value="" style="display: none;"></label>
									</div>
								<?php }  ?>

								<div class="signin-btn">
									<input type="button" class="btn btn-black continue-btn btn btn-primary" onclick="merchantLoginOtpEmail();" name="signin-btn" id="signin-continue-btn" value="Continue">
									<input type="submit" class="btn btn-blue d-none btn btn-primary" name="signin-btn" id="signin-btn" value="Login">
								
								</div><!-- signin-btn -->

							</div><!-- sigin-form -->
						</form>
					</div>
					<div role="tabpanel" class="tab-pane fade active in" id="register">
						<h4>New merchants, register here</h4>
						<h4>Please fill in all the fields to complete the registration.</h4>
						<form class="form-signin form-style" id="signup-user" action="<?php echo base_url();?>merchant/UserController/signUpPostData" method="POST">
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<input type="text" name="inputFirstName" id="inputFirstName" class="form-control" placeholder="First Name" value="" required>
										<span class="pmd-textfield-focused"></span>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<input type="text" name="inputLastName" id="inputLastName" class="form-control" placeholder="Last Name" value="" required>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<input type="email" name="inputEmail" id="inputEmail" class="form-control" placeholder="User ID/Email" value="<?php if(isset($_COOKIE["login_email"])) { echo $_COOKIE["login_email"]; } ?>" required autofocus>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<input type="text" name="inputTradeName" id="inputTradeName" placeholder="Trade Name" class="form-control" value="" required>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<input type="text" name="inputShopUrl" id="inputShopUrl" placeholder="Shop Url" class="form-control" value="" required>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<input type="text" name="inputBrnNumber" id="inputBrnNumber" placeholder="BRN Number" class="form-control" value="" required>
									</div>
								</div>
								<!-- <div class="col-sm-6">
									<div class="form-group">
										<input type="text" name="inputVatNumber" id="inputVatNumber" placeholder="VAT Number" class="form-control" value="" required>
									</div>
								</div> -->
								<div class="col-sm-6">
									<div class="form-group">
										<input type="password" name="inputPassword" id="inputPassword" placeholder="Password" class="form-control" value="<?php if(isset($_COOKIE["login_password"])) { echo $_COOKIE["login_password"]; } ?>" required>
										<span class="eye-text eye-password toggle-password"></span>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<input type="password" name="inputConfirmPassword" id="inputConfirmPassword" placeholder="Confirm Password" class="form-control" value="" required>
										<span class="eye-text eye-password toggle-Confpassword"></span>
									</div>
								</div>
								<div class="checkbox">
									<label class=""><input type="checkbox" name="newsletter_signin" id="newsletter_signin" > Sign Up for Newsletter <span class="checked"></span></label>
								</div>
								<div class="checkbox">
									<!-- <label class=""><input type="checkbox" name="remember" id="remember" <?php if(isset($_COOKIE["login_email"])) { ?> checked <?php } ?>> I've read and accept <span class="checked"></span><span class="required">terms and condition</span></label> -->
									 <label>
										<input type="checkbox" name="remember" id="remember"  required
											<?php if (isset($_COOKIE["login_email"])) { echo 'checked'; } ?>> 
										I have read and accept 
										<span class="checked"></span>
										<span class="required">
											<a href="/page/terms-conditions" target="_blank">
												terms and condition
											</a>
										</span>
									</label>
								</div>
							</div>
							<input class="btn btn-black btn btn-primary" type="submit" id="sign-up-btn" name="sign-up-btn" value="Sign Up">
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
	</div>
    <?php $this->load->view('common/footer'); ?>
<script type="text/javascript" src="<?php echo SKIN_JS2; ?>login.js"></script>
<script type="text/javascript" src="<?php echo SKIN_JS2; ?>register.js"></script>

<script type="text/javascript">
    // Checkout Page
    function merchantLoginOtpEmailCheckout() {
        var inputEmail = $('#inputEmail').val();
        $.ajax({
            url: BASE_URL + "CheckoutController/merchantLoginOtpEmail",
            type: "POST",
            data: {
                inputEmail: inputEmail
            },
            success: function(response) {
                var obj = JSON.parse(response);
                if (obj.flag == 1) {
                    $('.inputEmail').addClass('d-none');
                    $('.password').removeClass('d-none');
                    $('.continue-btn').addClass('d-none');
                    $('.btn-blue').removeClass('d-none');
                    $('.login-otp').html('Login OTP is :' + obj.data);
                } else if (obj.flag == 2) {
                    $('.inputEmail').addClass('d-none');
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



    function merchantLoginOtpEmail() {
        var inputEmail = $('#inputEmail').val();
        $.ajax({
            url: BASE_URL + "CustomerController/merchantLoginOtpEmail",
            type: "POST",
            data: {
                inputEmail: inputEmail
            },
            success: function(response) {
                var obj = JSON.parse(response);
                if (obj.flag == 1) {
                    $('.inputEmail').addClass('d-none');
                    $('.password').removeClass('d-none');
                    $('.continue-btn').addClass('d-none');
                    $('.btn-blue').removeClass('d-none');
                    $('.forgot-password').removeClass('d-none');

                } else if (obj.flag == 2) {
                    $('.inputEmail').addClass('d-none');
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
	<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script>
    function convertToPassword() {
        $('#password').attr('type', 'password');
    }
</script> -->
	</body>
</html>