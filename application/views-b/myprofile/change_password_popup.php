<div class="popup-form<?php echo (THEMENAME=='theme_zumbawear') ? ' grey-bg-user' : ''; ?>">
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">×</span>
</button>
	<div class="sign-in-inner">
		<h3>Change Password</h3>
		<h5>Please set your new password</h5>
		<form id="change-password-form" method="POST" action="<?php echo BASE_URL;?>MyProfileController/changePassword">
		<div class="forgotpassword-form ">
			<div class="form-box">
				<input class="form-control" type="password" name="old_password" id="old_password" placeholder="Old Password*">
			</div><!-- form-box -->
			<div class="form-box">
				<input class="form-control" type="password" name="new_password" id="new_password" placeholder="New Password*">
			</div><!-- form-box -->
			
			<div class="form-box">
				<input class="form-control" type="password" id="conf_password" name="conf_password" placeholder="Confirm Password">
			</div><!-- form-box -->
			
			<div class="signin-btn">
				<input type="submit" class="black-btn blue-btn tw-button-zumbared" name="change-password-btn" id="change-password-btn" value="Submit">
			</div><!-- signin-btn -->
		</div><!-- sigin-form -->
		</form>

	</div><!-- sign-in-inner -->
</div><!-- grey-bg-user -->
<script src="<?php echo SKIN_JS ?>change_password.js?v=<?php echo CSSJS_VERSION; ?>"></script>