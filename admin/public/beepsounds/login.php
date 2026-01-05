<?php $this->load->view('common_head'); ?>

<body class="sign-in">

	<div class="container-full">
		<div class="row h-100 m-rev">
			<div class="col-sm-5 d-flex align-items-center left-side text-center">
				<form class="form-signin form-style" id="login-user" action="<?php echo base_url(); ?>UserController/loginPost" method="POST">
					<h2 class="heading-color">Sign in</h2>
					<div class="form-fields text-left">
						<div class="mb-5">
							<label for="inputEmail" class="">User ID/Email <span class="required">*</span></label>
							<input type="email" name="inputEmail" id="inputEmail" class="form-control" value="<?php if (isset($_COOKIE["login_email"])) {
																													echo $_COOKIE["login_email"];
																												} ?>" required autofocus>
						</div>
						<div class="mb-5">
							<label for="inputPassword" class="">Password <span class="required">*</span></label>
							<input type="password" name="inputPassword" id="inputPassword" class="form-control" value="<?php if (isset($_COOKIE["login_password"])) {
																															echo $_COOKIE["login_password"];
																														} ?>" required>
							<span class="eye-password toggle-password"></span>
						</div>
						<div class="checkbox">
							<label>
								<input type="checkbox" value="remember-me">
							</label>
							<label class=""><input type="checkbox" name="remember" id="remember" <?php if (isset($_COOKIE["login_email"])) { ?> checked <?php } ?>> Remember me <span class="checked"></span></label>
							<!-- <a href="/forgot-password" class="link-it float-right">Forgot Password?</a> -->
						</div>
					</div>
					<input class="btn btn-lg btn-primary btn-block" type="submit" id="sign-in-btn" name="sign-in-btn" value="Sign in">
				</form>
			</div>
			<div class="col-sm-7 d-flex align-items-center right-side bkg-img">
				<div class="right-content">
					<h1 class="heading-welcome"><span class="fw-300">Welcome to</span> Yellow Markets</h1>
					<span class="sub-heading">Sign In to Access your Account</span>
				</div>
			</div>

		</div>
	</div>
	<script type="text/javascript" src="<?php echo SKIN_JS; ?>login.js"></script>
</body>

</html>