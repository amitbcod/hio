<?php $this->load->view('common/fbc-user/header'); ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="generator" content="Jekyll v4.1.1">
    <title>Settings</title>
 
	
	<script type="text/javascript">
	$(document).ready(function() {
	  const $valueSpan = $('.valueSpan');
	  const $value = $('#slider11');
	  $valueSpan.html($value.val());
	  $value.on('input change', () => {
		$valueSpan.html($value.val());
	  });
	  $('#shipment_countries').multiselect({

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
    <link href="<?php  echo base_url();?>public/css/style.css" rel="stylesheet">
    <link href="<?php  echo base_url();?>public/css/dashboard.css" rel="stylesheet">
    <link href="<?php  echo base_url();?>public/css/all.css" rel="stylesheet">
	
	<link href="<?php  echo base_url();?>public/css/flexslider.min.css" rel="stylesheet">
	<script src="<?php  echo base_url();?>public/js/jquery.flexslider-min.js"></script>
	
  </head>
  <body>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

	<ul class="nav nav-pills">
    <li class="active"><a data-toggle="pill" href="#taxes-list">Exceptional Taxes Settings</a></li>

  </ul>

  <div class="tab-content">
    
    <div id="add-new-tax-list" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name tax-set-head">Exceptional Taxes Settings</h1> 
        </div>
		
		
        <!-- form -->
        <div class="content-main form-dashboard">
		<p><?php echo $exceptional_tax_set_info->value; ?></p>
		<br>
			<div class="variant-common-block variant-list exceptional-setting">
			
				<h6>Select Categories</h6>
					<div class="accordion custom-accordion " id="custom-accordion-one"> 
		<?php if(isset($browse_category) && !empty($browse_category)) { ?>	
			<form method="POST" id="exceptional_tax_set_form" action="<?= base_url('UserController/update_exceptional_tax_set') ?>" novalidate="">
				<?php if (isset($exceptional_taxes_set_info) && $exceptional_taxes_set_info->id !='') { ?>
				<input type="hidden" name="row_id" value="<?php echo $exceptional_taxes_set_info->id ?>">
			<?php } ?>
				<div class="parent-menu-list-inner dis-flex-label">
					<ul class="common-list list-gc">
						<?php $num= count($browse_category); ?>
				<?php foreach($browse_category as $main_cat) { ?> 
						<li> 
							<label class="checkbox">
								<input type="checkbox"  name="chk_cat_menu[]" <?php echo (isset($main_cat['category_id']) && $main_cat['category_id']==$main_cat['id']) ? 'checked' : ''; ?> value="<?php echo $main_cat['id'] ?>" > <?php echo $main_cat['cat_name'];?><span class="checked"></span>
								<a class="custom-accordion-title d-block py-1" data-toggle="collapse" href="#subCatOuter_<?php echo $num; ?>" aria-expanded="false" aria-controls="subCatOuter_<?php echo $num; ?>">&nbsp;<i class="accordion-arrow fa fa-angle-down">&nbsp;</i></a>
							</label>
							<div id="subCatOuter_<?php echo $num; ?>" class="collapse " data-parent="#custom-accordion-one" >
						<?php if(isset($main_cat['cat_level_1'])) { ?>
							<ul>
								<?php foreach($main_cat['cat_level_1'] as $cat_level1) { ?>
								<li>
									<label class="checkbox">
										<input type="checkbox"  name="chk_cat_menu[]" <?php echo (isset($cat_level1['category_id']) && $cat_level1['category_id']==$cat_level1['id']) ? 'checked' : ''; ?> value="<?php echo $cat_level1['id'] ?>" > <?php echo $cat_level1['cat_name']; ?> <span class="checked"></span>
									</label>
									 <?php if(isset($cat_level1['cat_level_2'])) { ?>
							            <ul>
							              <?php foreach($cat_level1['cat_level_2'] as $cat_level2) { ?>
							                <li>
								                <label class="checkbox">
													<input type="checkbox"  name="chk_cat_menu[]" <?php echo (isset($cat_level2['category_id']) && $cat_level2['category_id']==$cat_level2['id']) ? 'checked' : ''; ?> value="<?php echo $cat_level2['id'] ?>" > <?php echo $cat_level2['cat_name']; ?> <span class="checked">
							                </li>
								              <?php } ?>
								        </ul>
								     <?php } ?>
								</li>
								
								 <?php } ?>
							</ul>
						<?php } ?>
							</div>
						</li>

						<?php  $num--; } ?>
						
					</ul>
						
				</div><!-- select-attributes -->
					
				<div class="form-group row tax-list-form">
					<div class="col-sm-12">
						<div class="col-sm-6">
							<label for="" class="col-sm-2 col-form-label font-500">Less than ( < )</label>
							<div class="col-sm-7 tax-mark-label">
							  <input type="number" name="less_than_amount" id="less_than_amount" class="form-control" value="<?php echo isset($exceptional_taxes_set_info->less_than_amount ) ? $exceptional_taxes_set_info->less_than_amount  : ''?>">
							  <span class="tax-mark">INR</span>
							</div>
						</div>
						<div class="col-sm-6">
							<label for="" class="col-sm-2 col-form-label font-500">Tax applied</label>
							<div class="col-sm-7 tax-mark-label">
							  <input type="number" name="less_than_tax_percent" id="less_than_tax_percent"  class="form-control" value="<?php echo isset($exceptional_taxes_set_info->less_than_amount ) ? $exceptional_taxes_set_info->less_than_tax_percent  : ''?>">
							   <span class="tax-mark">%</span>
							</div>
						</div>
					</div>
				</div><!-- form-group -->
			<?php if(empty($this->session->userdata('userPermission')) || in_array('system/exceptional_taxes_settings/write',$this->session->userdata('userPermission'))){  ?>
				<div class="download-discard-small mar-top">
					<button class="download-btn">Save</button>
				</div><!-- download-discard-small -->
			<?php } ?>
				 
				</form>
		<?php }else{ 
					echo "Not Available";
					} ?>	
				</div><!-- bs-example -->

			 	
			</div>
			</div>
        </div>
        <!--end form-->
    </div> <!-- dropshipping-products -->
</main>


<script type="text/javascript" src="<?php echo SKIN_JS; ?>exceptional_tax_set.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>