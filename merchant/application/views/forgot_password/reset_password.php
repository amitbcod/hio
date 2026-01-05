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
<body class="sign-in pass-reset">

<div class="container-full">
	<div class="row h-100 m-rev">
		<div class="col-sm-5 d-flex align-items-center left-side text-center">
			<form class="form-signin form-style" id="reset-password" action="<?php echo base_url();?>reset-password/<?php echo $urlData;?>" method="POST">
				<h2 class="heading-color">Password Reset</h2>
				<div class="form-fields text-left">
					  <div class="mb-5">
					  <label for="inputEmail" class="">Password <span class="required">*</span></label>
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
					  <div class="mb-5">
					  <label for="inputPassword" class="">Confirm Password <span class="required">*</span></label>
					  <input type="password" id="inputConfPassword" name="inputConfPassword" class="form-control" required>
					   <span class="eye-password toggle-Confpassword"></span>
					  </div>

				</div>
				<input class="btn btn-lg btn-primary btn-block" type="submit" id="reset-pass-btn" name="reset-pass-btn" value="Submit">
			</form>
		</div>
		<div class="col-sm-7 d-flex align-items-center right-side bkg-img">
			<div class="right-content">
				<h1 class="heading-welcome"><span class="fw-300">Welcome to</span> Shopin Shop</h1>
				<span class="sub-heading">Sign In to Access your Account</span>
			</div>
		</div>
    
	</div>
</div>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>reset_password.js"></script>
</body>

</html>
