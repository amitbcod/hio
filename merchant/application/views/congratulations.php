<!DOCTYPE html>
<html>
 <head>
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title><?php echo SITE_TITLE; ?><?php echo (isset($PageTitle) && $PageTitle!='')?' - '.$PageTitle:''; ?></title>
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
				<p style="color:white"><?php //echo $this->session->flashdata('verify_email_link');?></p>
			</div>
		</div>

		<div class="col-sm-5 d-flex align-items-center sup-right-side text-center">
			<form class="cong-content form-style">
				<h2 class="heading-color font-bold mb-5">Congratulations</h2>
				<p class="text-p">Verification Link have been sent to your email account. Proceed to confirm your registration</p>
				
			</form>
		</div>
    
	</div>
</div>
</body>

</html>
