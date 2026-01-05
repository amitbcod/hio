<!--<div class="popup-form<?php echo (THEMENAME=='theme_zumbawear') ? ' grey-bg-user' : ''; ?>">
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">×</span>
</button>
	<div class="sign-in-inner">
		<h3>Change Email</h3>
		<h5>Please set your new email</h5>
		<form id="change-email-form" method="POST" action="<?php echo BASE_URL;?>MyProfileController/changeEmail">
		<div class="forgotpassword-form ">
			
			<div class="form-box new-mail-popup">
				<input class="form-control" type="email" id="new_email" name="new_email" placeholder="New Email">
				<p class="error-msg"><span class="text-danger" id="error"></span></p>
			</div><!-- form-box -->
			
			<!--<div class="signin-btn">
				<input type="submit" class="black-btn blue-btn tw-button-zumbared" name="change-email-btn" id="change-email-btn" value="Submit">
			</div><!-- signin-btn -->
		<!--</div><!-- sigin-form -->
		<!--</form>
	</div><!-- sign-in-inner -->
<!--</div><!-- grey-bg-user -->


<div class="modal-header">

        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

		<h3 class="text-olderst">Change Email <span>Please set your new email</span></h3>

		<!-- <h5>Please set your new email</h5> -->

      </div>

      <div class="modal-body">

	  <form id="change-email-form" method="POST" action="<?php echo BASE_URL;?>MyProfileController/changeEmail">

          <div class="forgotpassword-form form-horizontal">

            <div class="form-group">

              <label for="old_password" class="col-sm-2 control-label">New Email</label>

              <div class="col-sm-10">

			  <input class="form-control" type="email" id="new_email" name="new_email" placeholder="New Email">
				<p class="error-msg"><span class="text-danger" id="error"></span></p>

              </div>

            </div>

            

            

            <div class="form-group">

              <div class="col-sm-offset-2 col-sm-10">

			  <input type="submit" class="black-btn blue-btn tw-button-zumbared btn btn-primary" name="change-email-btn" id="change-email-btn" value="Submit">

              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

              </div>

            </div>

          </div>

        </form>

      </div>
<script src="<?php echo SKIN_JS ?>change_email.js?v=<?php echo CSSJS_VERSION; ?>"></script>