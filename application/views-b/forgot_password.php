<?php $this->load->view('common/header'); ?>
    <div class="signin-full">
      <div class="container">
        <div class="col-md-12">
          <div class="row">
			
			<div class="grey-bg-user signin-section forgot-password-section">
				<div class="sign-in-inner">
					<h3>Forgot Password?</h3>
					<h5>Please enter your registered email address</h5>
					<form id="forgot-password-form" method="POST" action="<?php echo BASE_URL;?>customer/forgot-password">
					<div class="forgotpassword-form ">
						<div class="form-box">
							<input class="form-control" type="text" name="email" id="email" placeholder="Email*">
						</div><!-- form-box -->
						<div class="signin-btn">
							<input type="submit" class="black-btn blue-btn" name="forgot-password-btn" id="forgot-password-btn" value="Submit">
						</div><!-- signin-btn -->
					</div><!-- sigin-form -->
					</form>

				</div><!-- sign-in-inner -->
			</div><!-- grey-bg-user -->
			
		  </div><!-- row-->
		 </div><!-- col-md-12-->
		</div>
	</div><!-- signin-full -->
   
    <?php $this->load->view('common/footer'); ?>
	<script src="<?php echo SKIN_JS ?>forgot_password.js?v=<?php echo CSSJS_VERSION; ?>"></script>
	</body>
</html>