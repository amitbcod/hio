<?php $this->load->view('common/header'); ?>
    <div class="signin-full">
      <div class="container">
        <div class="col-md-12">
          <div class="row">

			<div class="grey-bg-user signin-section register-section">
				<div class="sign-in-inner">
					<form id="signup-form" method="POST" action="<?php echo BASE_URL;?>customer/register">
                       
	    				<h3>Create New Customer Account</h3>
                        <h5>Personal Information</h5>
						<?php (new Register('register'))->render(); ?>	
					</form>
				</div><!-- sign-in-inner -->
			</div><!-- grey-bg-user -->

		  </div><!-- row-->
		 </div><!-- col-md-12-->
		</div>
	</div><!-- signin-full -->

    <?php $this->load->view('common/footer'); ?>
	<script src="<?php echo SKIN_JS ?>register1.js?v=<?php echo CSSJS_VERSION; ?>"></script>
	</body>
</html>