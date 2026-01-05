<?php $this->load->view('common_head'); ?>
<body class="sign-in forgot-pass">

<div class="container-full">
	<div class="row h-100 m-rev">
		<div class="col-sm-5 d-flex align-items-center left-side text-center">
			<form class="form-signin form-style" id="forgot-password" action="<?php echo base_url();?>UserController/forgotPassword" method="POST">
				<h2 class="heading-color mb-2">Forgot Password</h2>
				<p class="text-p">Please enter your registered email address</p>
				<div class="form-fields text-left">
					  <div class="mb-5">
					  <input type="text" id="inputEmail" name="inputEmail" class="form-control" required autofocus>
					  <!--span class="eye-password"></span-->
					  </div>
				</div>
				<button class="btn btn-lg btn-primary btn-block" type="submit">Submit</button>
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
<script type="text/javascript" src="<?php echo SKIN_JS; ?>forgot_password.js"></script>
</body>

</html>
