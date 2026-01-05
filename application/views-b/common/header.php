<!DOCTYPE html>
<html lang="en">
  <head>
	
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	  	<meta name="viewport" content="target-densitydpi=device-dpi, initial-scale=1.0, user-scalable=no" />
      <meta http-equiv="Cache-control" content="public">
      <meta name="robots" content="noindex, nofollow" />
     <link rel="manifest" href="/manifest.json">
	  <?php $this->template->load('common_for_all/favicon'); ?>
		  <?php
	        $FinalPageTitle = (isset($PageTitle) && $PageTitle!='')?SITE_TITLE.' - '.$PageTitle:SITE_TITLE;
		      $PageMetaTitle = (isset($PageMetaTitle) && $PageMetaTitle!='')? $PageMetaTitle:'';
		      $PageMetaDesc = (isset($PageMetaDesc) && $PageMetaDesc!='')? strip_tags($PageMetaDesc):'';
		      $ProductData = (isset($ProductData) && $ProductData!='')? $ProductData:'';
		      $variableCommonHeader = array('FinalPageTitle' => $FinalPageTitle,'PageMetaTitle' => $PageMetaTitle,'PageMetaDesc' => $PageMetaDesc,'ProductData' => $ProductData);
			  
		  	//$this->template->load('common_for_all/header_meta_details',$variableCommonHeader);
		      $this->template->load('common_for_all/header_link_details');
		      $this->template->load('common_for_all/header_script');
			?>
		
			<?php
			    //  $headerscript = 'headerscript';
			    //  $headerscript = HomeDetailsRepository::get_static_block(SHOPCODE,SHOP_ID,$headerscript);
			    //    if(isset($headerscript) && $headerscript->statusCode == '200'){
			    //       foreach($headerscript->ShopStaticBlock as $block) {
			    //         echo htmlspecialchars_decode(stripslashes($block->content));
			    //     }
			    // }
			?>
			<style type="text/css">
				.site-navbar .site-logo a img {
		    max-width: 241px;
		    max-height: 90px;
		    float: left !important;
		    height: 112px;
		    margin-left: -79px;
		}

			</style>
  </head>

  <body>
    <?php $this->load->view('common/promobanner'); ?>
    <div class="site-wrap">
      <?php $this->load->view('common/navbar'); ?>
        
