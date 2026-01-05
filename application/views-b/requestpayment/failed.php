<?php $this->load->view('common/header'); ?>

<div class="breadcrum-section">
      <div class="container">
			<div class="breadcrum">
				<ul>
					<li><a href="<?php echo base_url(); ?>"><?=lang('home')?></a></li>
					<li><span class="icon icon-keyboard_arrow_right"></span></li>
					<li class="active"><?=lang('request_payment_failed')?></li>
					<!-- <li class="active"><?=lang('order_failed')?></li> -->
				</ul>
			</div>
        </div>
      </div>
<div class="car-page-full">
      <div class="container">
        <div class="col-md-12">
          <div class="row">
						<div class="thankyou-page failure">
							<div class="thankyou-check"><img src="<?php echo SKIN_URL; ?>images/failure.png"></div>
							<h2><?=lang('something_went_wrong_please_try_again')?> </h2>
							<!-- <div class="continue-shop-btn">
								<a href="<?php echo base_url(); ?>checkout"><button class="black-btn"><?=lang('go_back_to_checkout')?></button></a>
							</div> -->
						</div>
          </div><!-- row -->
        </div><!-- col-md-12 -->
      </div><!-- container -->
    </div>

<?php $this->load->view('common/footer'); ?>

<script>

</script> 
	
</body>
</html>