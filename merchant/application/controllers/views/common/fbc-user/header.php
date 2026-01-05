<!DOCTYPE html>

<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="generator" content="Jekyll v4.1.1">
    <meta name="robots" content="noindex, nofollow" />
    <title><?php echo SITE_TITLE; ?><?php echo (isset($PageTitle) && $PageTitle!='')?' - '.$PageTitle:''; ?></title>


    <!-- Bootstrap core CSS -->
  <link href="<?php echo SKIN_CSS; ?>bootstrap.min.css?v=<?php echo CSSJS_VERSION; ?>" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css" rel="stylesheet" />
	<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo SKIN_CSS; ?>flexslider.min.css?v=<?php echo CSSJS_VERSION; ?>" rel="stylesheet">
	<!-- JS File -->
    <script src="https://code.jquery.com/jquery-2.2.0.min.js" type="text/javascript"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.js" integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI=" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="<?php echo SKIN_JS; ?>bootstrap.min.js?v=<?php echo CSSJS_VERSION; ?>"></script>

	<!-- <script src="https://cdn.ckeditor.com/4.13.0/standard/ckeditor.js"></script> -->
  <script src="https://cdn.ckeditor.com/4.17.2/standard-all/ckeditor.js"></script>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js"></script>

	<!--<script src="<?php echo SKIN_JS; ?>sweetalert.js"></script>
	<script src="<?php echo SKIN_JS; ?>jquery.min.js"></script>-->
	<script src="<?php echo SKIN_JS; ?>jquery.validate.min.js?v=<?php echo CSSJS_VERSION; ?>"></script>
	<script src="<?php echo SKIN_JS; ?>additional-methods.min.js?v=<?php echo CSSJS_VERSION; ?>"></script>


	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" crossorigin="anonymous">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.10.4/typeahead.bundle.min.js"crossorigin="anonymous"></script>


	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js" /></script>

	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.22/datatables.min.css"/>

	<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.22/datatables.min.js"></script>

	<link rel="stylesheet" href="<?php echo SKIN_CSS; ?>jquery.multiselect.css?v=<?php echo CSSJS_VERSION; ?>" />
	<script src="<?php echo SKIN_JS; ?>jquery.multiselect.js?v=<?php echo CSSJS_VERSION; ?>"></script>

	<script src="<?php echo SKIN_JS; ?>jquery.flexslider-min.js?v=<?php echo CSSJS_VERSION; ?>"></script>
	<script type="text/javascript">
		const BASE_URL='<?= base_url() ?>';
	</script>

    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
    </style>
    <!-- Custom styles for this template -->
    <link href="<?php echo SKIN_CSS; ?>style.css?v=<?php echo CSSJS_VERSION; ?>" rel="stylesheet">
    <link href="<?php echo SKIN_CSS; ?>dashboard.css?v=<?php echo CSSJS_VERSION; ?>" rel="stylesheet">
    <link href="<?php echo SKIN_CSS; ?>all.css?v=<?php echo CSSJS_VERSION; ?>" rel="stylesheet">
  
  </head>
  <body>
    <?php $this->load->view('common/fbc-user/navbar'); ?>

<div class="container-fluid">
  <div class="row">
	<?php $this->load->view('common/fbc-user/sidebar'); ?>
