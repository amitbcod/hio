<!DOCTYPE html>
<html>
 <head>
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title> Shopin Shop </title>
  <meta charset="utf-8">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
	
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Manjari:wght@400;700&display=swap" rel="stylesheet">

   <!-- Bootstrap core CSS -->
	<link href="<?php echo SKIN_CSS; ?>bootstrap.min.css" rel="stylesheet">
	
	<!-- Template CSS File -->
	<link href="<?php echo SKIN_CSS; ?>style.css" rel="stylesheet">
	<link href="<?php echo SKIN_CSS; ?>signin.css" rel="stylesheet">

 </head>

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
			<form class="form-signin form-style">
				<h2 class="heading-color">Sign Up</h2>
				<div class="form-fields text-left">
					  <div class="mb-4">
					  <label for="inputName" class="">Organisation/Shop Name</label>
					  <input type="email" id="inputName" class="form-control" required autofocus>
					  </div>
					  <div class="mb-4">
					  <label for="inputEmail" class="">User ID</label>
					  <input type="email" id="inputEmail" class="form-control" required autofocus>
					  </div>
					  <div class="mb-4">
					  <label for="inputPassword" class="">Password</label>
					  <input type="password" id="inputPassword" class="form-control" required>
					   <span class="eye-password"></span>	
					  <span class="error-text">Incorrect Password</span>
					  </div>
					  <div class="checkbox">
						<label class=""><input type="checkbox"> Agree to all Terms and Conditions <span class="checked"></span></label>
					  </div>

				</div>
				<button class="btn btn-lg btn-primary btn-block" type="submit">Sign Up</button>
				<p class="text-user text-itc mt-4"> Already have an account ?  <a href="#" class="link">Sign In</a></p>
			</form>
		</div>
    
	</div>
</div>
</body>

</html>
