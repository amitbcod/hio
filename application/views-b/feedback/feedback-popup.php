<div class="popup-form<?php echo (THEMENAME=='theme_zumbawear') ? ' grey-bg-user' : ''; ?>">
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">×</span>
</button>
	<div class="sign-in-inner">
		<h3>Feedback</h3>
		<h5></h5>
		<form id="feedback-form" method="POST" action="<?php echo BASE_URL;?>CustomerController/open_feedback_popup">
		<div class="forgotpassword-form ">
			
			<div class="form-box new-mail-popup">
				<input class="form-control" type="text" id="name" name="name" placeholder="Your Full Name *">
				<p class="error-msg"><span class="text-danger" id="error"></span></p>
			</div><!-- form-box -->
			<div class="form-box new-mail-popup">
				<input class="form-control" type="email" id="email" name="email" placeholder="Your Email *">
				<p class="error-msg"><span class="text-danger" id="error"></span></p>
			</div><!-- form-box -->
			<div class="form-box new-mail-popup">
				<input class="form-control" type="text" id="where_here_abou_us" name="where_here_abou_us" placeholder="Where did you hear about us? *">
				<p class="error-msg"><span class="text-danger" id="error"></span></p>
			</div><!-- form-box -->

			<div class="form-box new-mail-popup">
				<textarea placeholder="Details *" name="details" class="form-control"></textarea>
				<p class="error-msg"><span class="text-danger" id="error"></span></p>
			</div><!-- form-box -->
			
			<div class="signin-btn">
				<input type="submit" class="black-btn blue-btn tw-button-zumbared" name="change-email-btn" id="change-email-btn" value="Submit">
			</div><!-- signin-btn -->
		</div><!-- sigin-form -->
		</form>
	</div><!-- sign-in-inner -->
</div><!-- grey-bg-user -->
<script src="<?php echo SKIN_JS ?>feedback.js?v=<?php echo CSSJS_VERSION; ?>"></script>