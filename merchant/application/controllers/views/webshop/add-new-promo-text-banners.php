<?php $this->load->view('common/fbc-user/header'); ?>
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <meta name="description" content="">
      <meta name="author" content="">
      <meta name="generator" content="Jekyll v4.1.1">
      <!-- <script type="text/javascript">
         $(document).ready(function() {
           const $valueSpan = $('.valueSpan');
           const $value = $('#slider11');
           $valueSpan.html($value.val());
           $value.on('input change', () => {
           $valueSpan.html($value.val());
           });
           $('#country_code').multiselect({
         
         placeholder: 'Select Countries',
         
         search: true
         
         }); 
           
         });
         
         $(document).ready(function() {
           const $valueSpan = $('.valueSpan2');
           const $value = $('#slider12');
           $valueSpan.html($value.val());
           $value.on('input change', () => {
           $valueSpan.html($value.val());
           });
         });
         
         $(document).ready(function(){
         $(".filter-section").hide();
           $(".filter button").click(function(){
           $(".filter-section").toggle();
           });
           $(".close-arrow").click(function(){
           $(".filter-section").hide();
           });
         });
         
         
         
         $( document ).ready(function() {
         $('.btn-slider').on('click', function() {
           $('.sliderPop').show();
           $('.ct-sliderPop-container').addClass('open');
           $('.sliderPop').addClass('flexslider');
           $('.sliderPop .ct-sliderPop-container').addClass('slides');
         
           $('.sliderPop').flexslider({
           selector: '.ct-sliderPop-container > .ct-sliderPop',
           slideshow: false,
           controlNav: false,
           controlsContainer: '.ct-sliderPop-container'
           });
         });
         
         $('.ct-sliderPop-close').on('click', function() {
           $('.sliderPop').hide();
           $('.ct-sliderPop-container').removeClass('open');
           $('.sliderPop').removeClass('flexslider');
           $('.sliderPop .ct-sliderPop-container').removeClass('slides');
         });
         
         
         });
         
         
      </script>
      <style>
         .customize-add-inner-sec label{display: inherit;}
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
      </style> -->
      <!-- Custom styles for this template -->
      <!-- <link href="<?php  echo base_url();?>public/css/style.css" rel="stylesheet">
      <link href="<?php  echo base_url();?>public/css/dashboard.css" rel="stylesheet">
      <link href="<?php  echo base_url();?>public/css/all.css" rel="stylesheet">
      <link href="<?php  echo base_url();?>public/css/flexslider.min.css" rel="stylesheet">
      <script src="<?php  echo base_url();?>public/js/jquery.flexslider-min.js"></script> -->
   </head>
   <body>
      <main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
         <ul class="nav nav-pills">
            <!-- <li><a href="<?= base_url('webshop/themes') ?>">Themes</a></li> -->
            <li><a href="<?= base_url('webshop/settings') ?>">Settings</a></li>
            <li><a href="<?= base_url('webshop/customize-pages') ?>">Customize Pages</a></li>
            <li><a href="<?= base_url('webshop/static-blocks') ?>">Static Blocks</a></li>
            <li><a href="<?= base_url('webshop/payment') ?>">Payments</a></li>
            <li><a href="<?= base_url('webshop/product-blocks') ?>">Product Blocks</a></li>
            <li class="active"><a href="<?= base_url('webshop/promo-text-banners') ?>">Promo Text Banners</a></li>
         </ul>
         <div class="tab-content">
            <div id="customize-tab" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
               <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                  <h1 class="head-name pad-bt-20"> &nbsp; <?php echo "Add New Promo Text Banners";  ?> </h1>
               </div>
               <!-- form -->
               <div class="content-main form-dashboard">
                  <form method="POST" action="<?= base_url('WebshopController/savePromoText')?>" id="promoBanner">
                     <input type="hidden" name="promoId" value="<?php if(isset($promoText)){ echo $promoText->id; } ?>">
                     <div class="customize-add-section">
                        <div class="row col-md-12">
                           <div class="col-sm-5 customize-add-inner-sec">
                              <label for="pageTitle">Banner Text *</label>
                              <input class="form-control" type="text" name="banner_text" id="banner_text" maxlength="500" value="<?php if(isset($promoText)){ echo $promoText->banner_text; } ?>" placeholder="Enter Banner Text here" required>
                             
                              <div class="clear pad-bt-40"></div>

                              <label for="pageTitle">Background Color *</label>
                              <input class="form-control" type="text" name="background_color" id="background_color" value="<?php if(isset($promoText)){ echo $promoText->background_color; } ?>" placeholder="Enter Background Color here" required>
                              <div class="clear pad-bt-40"></div>

                               <label for="pageTitle" class="">Text Color *</label>
                              <input class="form-control" type="text" name="text_color" id="text_color" value="<?php if(isset($promoText)){ echo $promoText->text_color; } ?>" placeholder="Enter Text Color here" required>
                              <div class="clear pad-bt-40"></div>
                              <label for="status">Status *</label>
                              <div class="customize-add-radio-section row">
                                 <?php 
                                    $published = 'checked';
                                    $hold = '';
                                    if(isset($promoText))
                                    { 
                                      if($promoText->status == 2)
                                      {
                                        $published = '';
                                        $hold = 'checked';
                                      }
                                    }
                                    ?>
                                 <div class="radio col-sm-6">
                                    <label><input type="radio" name="status" value="1" id="published" <?= $published ?>>Published <span class="checkmark"></span></label>
                                 </div>
                                 <!-- radio -->
                                 <div class="radio col-sm-6">
                                    <label><input type="radio" name="status" value="2" id="hold" <?= $hold ?>>On-Hold <span class="checkmark"></span></label>
                                 </div>
                                 <!-- radio -->
                              </div>
                              <!-- customize-add-radio-section -->
                           </div>
                           <!-- col-sm-6 -->  
                        </div>
                        <!-- row -->
                     </div>
                     <!-- customize-add-section -->
                     <div class="download-discard-small ">
                        <a class="btn white-btn" href="<?php echo  base_url('webshop/promo-text-banners'); ?>">Back</a>
                 
                        <button class="download-btn" type="submit"><?php if(isset($promoText)){ echo "Update"; }else{ echo "Save"; }?></button>
                   
                     </div>
                     <!-- download-discard-small  -->
                  </form>
               </div>
               <!--end form-->
            </div>
         </div>
      </main>
    
      <script src="<?php echo SKIN_JS; ?>webshop.js"></script>
      <?php $this->load->view('common/fbc-user/footer'); ?>