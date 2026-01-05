<?php $this->load->view('common/header'); ?>
    <div class="signin-full">
      <div class="container">
        <div class="col-md-12">
          <div class="row">

			<div class="grey-bg-user signin-section">
				<div class="sign-in-inner">
					<h3><?=lang('sign_in_with_zumba_title')?></h3>

					<h5><?=lang('sign_in_with_zumba_request_email_subtitle')?></h5>
					<p><?=lang('sign_in_with_zumba_request_email_message')?></p>
					<form method="POST" action="<?php echo BASE_URL;?>oauth/zumba/confirm_user_email">
                        <input type="hidden" name="instructor_id" value="<?=$instructor_id?>">
					<div class="sigin-form">
						<div class="form-box">
							<input class="form-control" type="email" name="email" id="email" placeholder="<?=lang('email')?>" required>
						</div>

						<div class="signin-btn">
							<input type="submit" class="btn btn-blue" name="signin-btn" id="signin-btn" value="<?=lang('confirm')?>">
						</div><!-- signin-btn -->

					</div><!-- sigin-form -->
					</form>


		        </div>
		    </div>
		    </div>
	    </div>
      </div>
    </div>
    <?php $this->load->view('common/footer'); ?>

	</body>
</html>