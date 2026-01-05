<?php $this->load->view('common/header'); ?>
	<div class="row">
          <div class="col-md-6 col-md-offset-3">
            <div class="content-page shadow margbot20">
              <ul class="nav nav-tabs" role="tablist">
                  <li role="presentation" class=""><a href="#login" aria-controls="login" role="tab" data-toggle="tab" aria-expanded="true">Login</a></li>
                  <li role="presentation" class="active"><a href="#register" aria-controls="register" role="tab" data-toggle="tab" aria-expanded="false">Register</a></li>
              </ul>
			  <div class="tab-content">
					<div role="tabpanel" class="tab-pane fade " id="login">
							<h4>Existing shopee, log in with your email </h4>
							<form id="sigin-form" method="POST" action="<?php echo BASE_URL;?>customer/login" style="margin-top:10px;">
								<?php (new Login('login'))->render(); ?>
							</form>
					</div>
					<div role="tabpanel" class="tab-pane fade active in" id="register">
                      <h4>New shopee, register here</h4>
                      <h4>Please fill in all the fields to complete the registration.</h4>
					  <form id="signup-form" method="POST" action="<?php echo BASE_URL;?>customer/register" style="margin-top:10px;">
					  	<?php (new Register('register'))->render(); ?>	
					  </form>
					</div>
				</div>
			</div>
		</div>
	</div>
    <?php $this->load->view('common/footer'); ?>
	<script src="<?php echo SKIN_JS ?>register1.js?v=<?php echo CSSJS_VERSION; ?>"></script>
	<script src="<?php echo SKIN_JS ?>login.js?v=<?php echo CSSJS_VERSION; ?>"></script>
	<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script>
    function convertToPassword() {
        $('#password').attr('type', 'password');
    }
</script> -->
	</body>
</html>