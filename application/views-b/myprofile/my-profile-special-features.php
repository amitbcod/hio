<?php $this->load->view('common/header'); ?>
<div class="breadcrum-section">
      <div class="container">
			<div class="breadcrum">
				<ul>
					<li><a href="<?php echo base_url(); ?>"><?=lang('bred_home')?></a></li>
					<li><span class="icon icon-keyboard_arrow_right"></span></li>
					<li class="active"><?=lang('my_profile')?></li>
				</ul>
			</div>
        </div>
      </div><!-- breadcrum section -->


     <div class="my-profile-page-full">
      <div class="container">
          <div class="row">
				<?php $this->load->view('common/profile_sidebar'); ?>

				<div class="col-md-9 col-lg-9 ">
					<h4 class="manage-add-head"><?=lang('special_features')?>  </h4>

					<div class="personal-info-form col-sm-12 special-features">
                        <div class="special-features-inner">
                            <div class="personal-info-btn col-sm-12">
                                <?php if (isset($customerData->access_prelanch_product) && $customerData->access_prelanch_product == 1) { ?>
                                    <button class="orange-btn-outline"><a href="<?php echo base_url()."pre-launch" ?>"><?=lang('view_pre_launch_products')?></a></button>
                                <?php 	} 	?>
                                &nbsp;
                                <?php if (isset($customerData->allow_catlog_builder) && $customerData->allow_catlog_builder == 1) { ?>
                                    <a href="<?php echo base_url()."customer/upc-catlog-listing" ?>"><button class="black-btn"><?=lang('catelog_builder')?></button></a>
                                <?php 	} 	?>
                            </div>
                        </div>
                    </div>
				</div><!-- col-md-9 -->

          </div><!-- row -->
      </div><!-- container -->
    </div><!-- my-profile-page-full -->

 <?php $this->load->view('common/footer'); ?>

   <!-- <script src="<?php echo SKIN_JS ?>special_features.js?v=<?php echo CSSJS_VERSION; ?>"></script> -->
