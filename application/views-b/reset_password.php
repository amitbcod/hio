<?php $this->load->view('common/header'); ?>
    <div class="signin-full">
      <div class="container">
        <div class="col-md-12">
          <div class="row">
			
			<div class="grey-bg-user signin-section forgot-password-section">
				<div class="sign-in-inner">
					<h3>Reset Password</h3>
					<h5>Please set your new password</h5>
					<form id="reset-password-form" method="POST" action="<?php echo BASE_URL;?>customer/reset-password/<?php echo $urlData;?>">
					<div class="forgotpassword-form ">
						<div class="form-box">
							<input class="form-control" type="password" name="password" id="password" placeholder="Password">
						</div><!-- form-box -->
						
						<div class="form-box">
							<input class="form-control" type="password" id="conf_password" name="conf_password" placeholder="Confirm Password">
						</div><!-- form-box -->
						
						<div class="signin-btn">
							<input type="submit" class="black-btn blue-btn" name="reset-password-btn" id="reset-password-btn" value="Submit">
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
	<script src="<?php echo SKIN_JS ?>reset_password.js?v=<?php echo CSSJS_VERSION; ?>"></script>
	</body>
</html>