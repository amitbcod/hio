<?php $this->load->view('common_head'); ?>
<body class="sign-in">

<div class="container-full">
	<div class="row h-100 m-rev">
		<div class="col-sm-5 d-flex align-items-center left-side text-center">
			<form class="form-signin form-style" id="signup-user" action="<?php echo base_url();?>UserController/signUpPostData" method="POST">
				<h2 class="heading-color">Sign in</h2>
				<div class="form-fields text-left">
					<div class="mb-5">
						<label for="inputFirstName" class="">First Name <span class="required">*</span></label>
						<input type="text" name="inputFirstName" id="inputFirstName" class="form-control" value="" required>
					</div>
					<div class="mb-5">
						<label for="inputLastName" class="">Last Name <span class="required">*</span></label>
						<input type="text" name="inputLastName" id="inputLastName" class="form-control" value="" required>
					</div>
					<div class="mb-5">
						<label for="inputEmail" class="">User ID/Email <span class="required">*</span></label>
						<input type="email" name="inputEmail" id="inputEmail" class="form-control" value="<?php if(isset($_COOKIE["login_email"])) { echo $_COOKIE["login_email"]; } ?>" required autofocus>
					</div>
					<div class="mb-5">
						<label for="inputTradeName" class="">Trade Name <span class="required">*</span></label>
						<input type="text" name="inputTradeName" id="inputTradeName" class="form-control" value="" required>
					</div>
					<div class="mb-5">
						<label for="inputShopUrl" class="">Shop Url <span class="required">*</span></label>
						<input type="text" name="inputShopUrl" id="inputShopUrl" class="form-control" value="" required>
					</div>
					<div class="mb-5">
						<label for="inputBrnNumber" class="">BRN Number <span class="required">*</span></label>
						<input type="text" name="inputBrnNumber" id="inputBrnNumber" class="form-control" value="" required>
					</div>
					<div class="mb-5">
						<label for="inputVatNumber" class="">VAT Number <span class="required">*</span></label>
						<input type="text" name="inputVatNumber" id="inputVatNumber" class="form-control" value="" required>
					</div>
					<div class="mb-5">
					  <label for="inputPassword" class="">Password <span class="required">*</span></label>
					  <input type="password" name="inputPassword" id="inputPassword" class="form-control" value="<?php if(isset($_COOKIE["login_password"])) { echo $_COOKIE["login_password"]; } ?>" required>
					  <span class="eye-text eye-password toggle-password"></span>
					</div>
					<div class="mb-5">
					  <label for="inputConfirmPassword" class="">Confirm Password <span class="required">*</span></label>
					  <input type="password" name="inputConfirmPassword" id="inputConfirmPassword" class="form-control" value="" required>
					  <span class="eye-text eye-password toggle-Confpassword"></span>
					</div>
					<div class="checkbox">
						<label>
						  <input type="checkbox" value="remember-me"> 
						</label>
						<label class=""><input type="checkbox" name="remember" id="remember" <?php if(isset($_COOKIE["login_email"])) { ?> checked <?php } ?>> Sign Up for Newsletter <span class="checked"></span></label>
					</div>
					<div class="checkbox">
						<label>
						  <input type="checkbox" value="remember-me"> 
						</label>
						<label class=""><input type="checkbox" name="remember" id="remember" <?php if(isset($_COOKIE["login_email"])) { ?> checked <?php } ?>> I've read and accept <span class="checked"></span><span class="required">terms and condition*</span></label>
					</div>
				</div>
				<input class="btn btn-lg btn-primary btn-block" type="submit" id="sign-up-btn" name="sign-up-btn" value="Sign Up">
			</form>
		</div>
		<div class="col-sm-7 d-flex align-items-center right-side bkg-img">
			<div class="right-content">
				<span class="sub-heading">Already have a merchant account?</span>
			</div>
		</div>
    
	</div>
</div>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>register.js"></script>
</body>

</html>
