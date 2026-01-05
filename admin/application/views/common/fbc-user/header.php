<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="generator" content="Jekyll v4.1.1">
  <meta name="robots" content="noindex, nofollow" />
  <title><?php echo SITE_TITLE; ?><?php echo (isset($PageTitle) && $PageTitle != '') ? ' - ' . $PageTitle : ''; ?></title>


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

  <!-- <script src="https://cdn.ckeditor.com/ckeditor5/46.0.1/ckeditor5.umd.js"></script>   -->
   <script src="https://cdn.ckeditor.com/4.17.2/standard-all/ckeditor.js"></script> 
 
  <!-- <script src="<?php echo SKIN_JS; ?>ckeditor.js"></script> -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js"></script>

  <!--<script src="<?php echo SKIN_JS; ?>sweetalert.js"></script>
	<script src="<?php echo SKIN_JS; ?>jquery.min.js"></script>-->
  <script src="<?php echo SKIN_JS; ?>jquery.validate.min.js?v=<?php echo CSSJS_VERSION; ?>"></script>
  <script src="<?php echo SKIN_JS; ?>additional-methods.min.js?v=<?php echo CSSJS_VERSION; ?>"></script>


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" crossorigin="anonymous">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.10.4/typeahead.bundle.min.js" crossorigin="anonymous"></script>


  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js" />
  </script>

  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />


  <!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.22/datatables.min.css" />

  <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.22/datatables.min.js"></script> -->

  <link rel="stylesheet" type="text/css" href="https://shop1.shopinshop.co/public/css/dt-11022-datatables.min.css?v=22072501"/>
 
<script type="text/javascript" src="https://shop1.shopinshop.co/public/js/dt-11022-datatables.min.js?v=22072501"></script>

  <link rel="stylesheet" href="<?php echo SKIN_CSS; ?>jquery.multiselect.css?v=<?php echo CSSJS_VERSION; ?>" />
  <script src="<?php echo SKIN_JS; ?>jquery.multiselect.js?v=<?php echo CSSJS_VERSION; ?>"></script>

  <script src="<?php echo SKIN_JS; ?>jquery.flexslider-min.js?v=<?php echo CSSJS_VERSION; ?>"></script>
  <script type="text/javascript">
    const BASE_URL = '<?= base_url() ?>';
  </script>
  <!--    -->


  <!--    -->
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

    /* price range css  start*/
    body {
      font-family: 'Karla', 'Arial', sans-serif;
      font-weight: 500;
      background: #fff;
    }

    p {
      padding: 0;
      margin: 0;
    }

    .wrapper {
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }

    .filter-price {
      width: 220px;
      border: 0;
      padding: 0;
      margin: 0;
    }

    .price-title {
      position: relative;
      color: #fff;
      font-size: 14px;
      line-height: 1.2em;
      font-weight: 400;
      background: #d58e32;
      padding: 10px;
    }

    .price-container {
      display: flex;
      border: 1px solid #ccc;
      padding: 5px;
      margin-left: 57px;
    }

    .price-field {
      position: relative;
      width: 100%;
      height: 36px;
      box-sizing: border-box;
      padding-top: 15px;
      padding-left: 0px;
    }

    .price-field input[type=range] {
      position: absolute;
    }

    /* Reset style for input range */

    .price-field input[type=range] {
      width: 100%;
      height: 7px;
      border: 1px solid #000;
      outline: 0;
      box-sizing: border-box;
      border-radius: 5px;
      pointer-events: none;
      -webkit-appearance: none;
    }

    .price-field input[type=range]::-webkit-slider-thumb {
      -webkit-appearance: none;
    }

    .price-field input[type=range]:active,
    .price-field input[type=range]:focus {
      outline: 0;
    }

    .price-field input[type=range]::-ms-track {
      width: 188px;
      height: 2px;
      border: 0;
      outline: 0;
      box-sizing: border-box;
      border-radius: 5px;
      pointer-events: none;
      background: transparent;
      border-color: transparent;
      color: red;
      border-radius: 5px;
    }

    /* Style toddler input range */

    .price-field input[type=range]::-webkit-slider-thumb {
      /* WebKit/Blink */
      position: relative;
      -webkit-appearance: none;
      margin: 0;
      border: 0;
      outline: 0;
      border-radius: 50%;
      height: 10px;
      width: 10px;
      margin-top: -4px;
      background-color: #fff;
      cursor: pointer;
      cursor: pointer;
      pointer-events: all;
      z-index: 100;
    }

    .price-field input[type=range]::-moz-range-thumb {
      /* Firefox */
      position: relative;
      appearance: none;
      margin: 0;
      border: 0;
      outline: 0;
      border-radius: 50%;
      height: 10px;
      width: 10px;
      margin-top: -5px;
      background-color: #fff;
      cursor: pointer;
      cursor: pointer;
      pointer-events: all;
      z-index: 100;
    }

    .price-field input[type=range]::-ms-thumb {
      /* IE */
      position: relative;
      appearance: none;
      margin: 0;
      border: 0;
      outline: 0;
      border-radius: 50%;
      height: 10px;
      width: 10px;
      margin-top: -5px;
      background-color: #242424;
      cursor: pointer;
      cursor: pointer;
      pointer-events: all;
      z-index: 100;
    }

    /* Style track input range */

    .price-field input[type=range]::-webkit-slider-runnable-track {
      /* WebKit/Blink */
      width: 188px;
      height: 2px;
      cursor: pointer;
      background: #555;
      border-radius: 5px;
    }

    .price-field input[type=range]::-moz-range-track {
      /* Firefox */
      width: 188px;
      height: 2px;
      cursor: pointer;
      background: #242424;
      border-radius: 5px;
    }

    .price-field input[type=range]::-ms-track {
      /* IE */
      width: 188px;
      height: 2px;
      cursor: pointer;
      background: #242424;
      border-radius: 5px;
    }

    /* Style for input value block */

    .price-wrap {
      display: flex;
      color: #242424;
      font-size: 14px;
      line-height: 1.2em;
      font-weight: 400;
      margin-bottom: 0px;
    }

    .price-wrap-1,
    .price-wrap-2 {
      display: flex;
      margin-left: 0px;
    }

    .price-title {
      margin-right: 5px;
    }

    .price-wrap_line {
      margin: 6px 0px 5px 5px;
    }

    .price-wrap #one,
    .price-wrap #two {
      width: 30px;
      text-align: right;
      margin: 0;
      padding: 0;
      margin-right: 2px;
      background: 0;
      border: 0;
      outline: 0;
      color: #242424;
      font-family: 'Karla', 'Arial', sans-serif;
      font-size: 14px;
      line-height: 1.2em;
      font-weight: 400;
    }

    .price-wrap label {
      text-align: right;
      margin-top: 6px;
      padding-left: 5px;
    }

    /* Style for active state input */

    .price-field input[type=range]:hover::-webkit-slider-thumb {
      box-shadow: 0 0 0 0.5px #242424;
      transition-duration: 0.3s;
    }

    .price-field input[type=range]:active::-webkit-slider-thumb {
      box-shadow: 0 0 0 0.5px #242424;
      transition-duration: 0.3s;
    }

    /* price range css end */
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