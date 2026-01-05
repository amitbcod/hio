<?php $this->load->view('common_head'); ?>
<style>
/* Add a green text color and a checkmark when the requirements are right */
.valid {
  color: green;
}

.valid:before {
  position: relative;
  left: -35px;
  content: "✔";
}

/* Add a red text color and an "x" when the requirements are wrong */
.invalid {
  color: red;
}

.invalid:before {
  position: relative;
  left: -35px;
  content: "✖";
}
</style>
<body class="sign-up">

<div class="container-full">
	<div class="row h-100 m-rev">
		
		<div class="col-sm-7 d-flex align-items-center sup-left-side bkg-img">
			<div class="right-content">
				<h1 class="heading-welcome"><span class="fw-300">Welcome to</span> Shopin Shop</h1>
				<span class="sub-heading">Sign In to Access your Account</span>
			</div>
		</div>
		
		<div class="col-sm-5 d-flex align-items-center sup-right-side text-center">
			<form class="form-signin form-style" id="signup-user" method="POST" action="<?php echo base_url()?>UserController/signUpPostData">
				<h2 class="heading-color">Sign Up</h2>
				<div class="form-fields text-left">
				  <div class="mb-4">
				  <label for="inputName" class="">Organisation/Shop Name <span class="required">*</span></label>
				  <input type="name" id="inputName" name="inputName" class="form-control validate-char" required autofocus>
				  </div>
				  <div class="mb-4">
				  <label for="inputEmail" class="">User ID <span class="required">*</span></label>
				  <input type="email" id="inputEmail" name="inputEmail" class="form-control" required autofocus>
				  </div>
				  <div class="mb-4">
				  <label for="inputPassword" class="">Password <span class="required">*</span></label>
				  <input type="password" id="inputPassword" name="inputPassword" class="form-control" required>
				  <span class="eye-password toggle-password"></span>	
				  <div id="message" style=" display:none;">
					  <p><strong>Password must contain the following:</strong></p>
					  <p id="alphabetic" class="invalid">One <b>alphabetic</b> char</p>
					  <p id="special" class="invalid">One <b>special</b> char</p>
					  <p id="number" class="invalid">One <b>number</b></p>
					  <p id="length" class="invalid">Minimum <b>8 characters</b></p>
			   	  </div>
				  </div>
				  <div class="mb-4">
				  <label for="country" class="">Country <span class="required">*</span></label>
				  <select name="country" id="country" class="country form-control">
					<option value="" selected>Select Country</option>
					<?php if(isset($countryList) && count($countryList) > 0) { 
						foreach($countryList as $data) { ?>
							<option value="<?php echo $data['country_code']; ?>" <?php echo $data['country_code'] == $countryCode ? "selected='selected'" : ''; ?>><?php echo $data['country_name']; ?></option>
					<?php } } ?>
				  </select>	
				  </div>

				  <div class="mb-4">
				  <label for="currency" class="">Currency <span class="required">*</span></label>
				  <select name="currency" id="currency" class="currency form-control">
					<option value="" selected>Select Currency</option>
					<?php if(isset($currencyList) && count($currencyList) > 0) { 
						foreach($currencyList as $data) { ?>
							
							<option value="<?php echo $data['currency_code'].'/'.$data['currency']; ?>" <?php echo isset($currencySymbol->currency_code) && ($data['currency_code'] == $currencySymbol->currency_code) ? "selected='selected'" : ''; ?>><?php echo $data['currency_name']; ?></option>
							
					<?php } } ?>
				  </select>	
				  </div>

				  <div class="checkbox">
					<label class=""><input type="checkbox" id="inputTerms" name="inputTerms"> Agree to all Terms and Conditions <span class="checked"></span><span class="required">*</span></label>
				  </div>
				  <!-- Google reCAPTCHA box -->
					<div class="form-group captcha-code">
					<div class="g-recaptcha" data-sitekey="<?php echo GC_SITE_KEY; ?>" data-callback="recaptchaCallback"></div>
					<input type="hidden" class="hiddenRecaptcha required" name="hiddenRecaptcha" id="hiddenRecaptcha">
					</div>

				</div>
				<input class="btn btn-lg btn-primary btn-block" type="submit" id="sign-up-btn" name="sign-up-btn" value="Sign Up">
				<p class="text-user text-itc mt-4"> Already have an account ?  <a href="<?php echo BASE_URL;?>" class="link">Sign In</a></p>
			</form>
		</div>
    
	</div>
</div>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>register.js"></script>
</body>

</html>
