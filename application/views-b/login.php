<?php $this->load->view('common/header'); ?>
    <div class="signin-full">
      <div class="container">
        <div class="col-md-12">
          <div class="row">

			<div class="grey-bg-user signin-section">
				<div class="sign-in-inner">
					<h3>Customer Login</h3>

					<h5>Registered Users</h5>
					<p>If you have an account, sign in with your email address.</p>
					<form id="sigin-form" method="POST" action="<?php echo BASE_URL;?>customer/login">
					<?php (new Login('login'))->render(); ?>
					</form>

					<div class="new-customer">
						<h3><?=lang('new_customer')?></h3>
						<p><?=lang('new_customer_msg')?></p>
						<div class="signin-btn">
						
							<a href="<?php echo BASE_URL?>customer/register"> <button class="black-btn">Create an account</button> </a>
				
						</div><!-- signin-btn -->
					</div><!-- new-customer -->
				</div><!-- sign-in-inner -->
			</div><!-- grey-bg-user -->

		  </div><!-- row-->
		 </div><!-- col-md-12-->
		</div>
	</div>

    <?php $this->load->view('common/footer'); ?>
	<script src="<?php echo SKIN_JS ?>login.js?v=<?php echo CSSJS_VERSION; ?>"></script>
	</body>
</html>