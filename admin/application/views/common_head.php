<!DOCTYPE html>
<html>

<head>
	<meta content="width=device-width, initial-scale=1.0" name="viewport">

	<title><?php echo SITE_TITLE; ?><?php echo (isset($PageTitle) && $PageTitle != '') ? ' - ' . $PageTitle : ''; ?></title>
	<meta charset="utf-8">
	<meta name="Keywords" content="">
	<meta name="Description" content="">
	<meta name="robots" content="noindex, nofollow" />
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Manjari:wght@400;700&display=swap" rel="stylesheet">

	<!-- Bootstrap core CSS -->
	<link href="<?php echo SKIN_CSS; ?>bootstrap.min.css" rel="stylesheet">

	<!-- Template CSS File -->
	<link href="<?php echo SKIN_CSS; ?>style.css" rel="stylesheet">
	<link href="<?php echo SKIN_CSS; ?>signin.css" rel="stylesheet">

	<!-- sweetalert File -->
	<!--link href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css" rel="stylesheet" /-->

	<!-- JS File -->
	<script src="<?php echo SKIN_JS; ?>jquery.min.js"></script>
	<script src="<?php echo SKIN_JS; ?>jquery.validate.min.js"></script>
	<script src="<?php echo SKIN_JS; ?>additional-methods.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>


	<script>
		const BASE_URL = '<?= base_url() ?>';
		<?php if (is_logged_in()) : ?>
			const S3_URL = '<?= get_s3_url('') ?>';
		<?php endif; ?>
	</script>
</head>