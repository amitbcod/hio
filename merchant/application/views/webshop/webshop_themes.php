<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<ul class="nav nav-pills">
    	<!-- <li class="active"><a href="<?= base_url('webshop/themes') ?>">Themes</a></li> -->
    	<li><a href="<?= base_url('webshop/settings') ?>">Settings</a></li>
    	<li><a href="<?= base_url('webshop/customize-pages') ?>">Customize Pages</a></li>
		<li><a href="<?= base_url('webshop/static-blocks') ?>">Static Blocks</a></li>
		<li><a href="<?= base_url('webshop/payment') ?>">Payments</a></li>
		<li><a href="<?= base_url('webshop/product-blocks') ?>">Product Blocks</a></li>
		<li class=""><a href="<?= base_url('webshop/promo-text-banners') ?>">Promo Text Banners</a></li>
		
  	</ul>

  	<div class="tab-content">
    	<div id="theme-tab" class="tab-pane fade in active common-tab-section big-search min-height-480" style="opacity:1;">
      		<!-- <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
			 	<div class="float-right product-filter-div">
			  		<div class="search-div">
				  		<input class="form-control form-control-dark top-search" type="text" placeholder="Search for Themes" aria-label="Search" value="">
				  		<button type="button" class="btn btn-sm search-icon"><i class="fas fa-search"></i></button>
			 		</div>
				</div>
		 		 
	     	</div> -->
			<h1 class="head-name">Popular Themes </h1> 
        	<!-- form -->
	        <div class="content-main form-dashboard">
	            <div class="theme-listing">
					<ul>
						<?php 
						foreach ($themes as $themArr) { ?>
							<?php 
							if(isset($themeID) && $themeID->theme_id == $themArr->id){ ?>
								<li class="current-active">
							<?php }else{ ?>
								<li>
							<?php } ?>
							
								<div class="theme-listing-img-block">
									<img src="<?= base_url('/public/uploads/themes/').'theme'.$themArr->id.'/'.$themArr->defualt_thumb?>">
									<div class="view-demo">
										<a href="#" class="btn-slider" id="slider_<?= $themArr->id ?>">View Demo</a>
									</div><!-- view-demo -->
								</div><!-- theme-listing-img-block -->
								<div class="theme-listing-action-btn-block">
									<span class="theme-title"><?= $themArr->theme_name ?>(<?= $themArr->theme_code ?>)</span>
									<span class="theme-action-btn">
										<?php 
										if(isset($themeID) && $themeID->theme_id == $themArr->id){ ?>
											<a>Current</a>
										<?php }else{ ?>
											<a class="themeClass" data-toggle="modal" data-target="#themeUse" data-id="<?= $themArr->id ?>">Use</a>
										<?php } ?>
										
								</span>
								</div><!-- theme-listing-action-btn-block -->
							</li>
						<?php } ?>
					</ul>
				</div><!-- theme-listing -->
	        </div>
	        <!--end form-->
	    </div>
		<!-- popup -->
		<?php 
		foreach ($themes as $themSlid) { ?>
		<div class="sliderPop slider_<?= $themSlid->id ?>" id="slider_<?= $themSlid->id ?>" style="display:none;">
			<div class="slider-header">
				<a class="ct-sliderPop-close" href="#"><i class="fas  fa-angle-left"></i> &nbsp; Back</a>
				<span class="slider-header-logo">LOGO</span>
				<?php 
				if(isset($themeID) && $themeID->theme_id == $themSlid->id){ ?>
					<button class="purple-btn float-right use-template-btn ">Current Template</button>
				<?php }else{ ?>
					<button class="purple-btn float-right use-template-btn themeClass" data-toggle="modal" data-target="#themeUse" data-id="<?= $themSlid->id ?>">Use Template</button>
				<?php } ?>
				
			</div>
			<?php $json = json_decode($themSlid->demo_images); ?>
			<div class="ct-sliderPop-container">
				<?php foreach ($json as $key => $value) { ?>
	    		<div class="ct-sliderPop ct-sliderPop-slide1 open">
	     			<img src="<?= base_url('/public/uploads/themes/').'theme'.$themSlid->id.'/'.$value?>">
	    		</div>
	    		<?php } ?>
	  		</div>
		</div>
		<?php } ?>
		<!-- popup end --->
  	</div>        
</main>
<div class="modal fade" id="themeUse" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="z-index: 99999999;">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="themeForm" method="POST" action="<?= base_url('WebshopController/changeTheme')?>">
				<input type="hidden" name="themeID" id="themeID">
				<div class="modal-header">
					<h1 class="head-name">Are you sure? you want to change theme!</h1>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-footer">
					<button type="button" data-dismiss="modal" aria-label="Close" class="white-btn">No</button>
					<button class="purple-btn">Change</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script src="<?php echo SKIN_JS; ?>webshop.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>