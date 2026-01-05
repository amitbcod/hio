<?php $this->load->view('common/header'); ?>
<div class="breadcrum-section">
      <div class="container">
			<div class="breadcrum">
				<ul>
					<li><a href="<?php echo base_url(); ?>"><?=lang('home')?></a></li>
					<li><span class="icon icon-keyboard_arrow_right"></span></li>
					<li class=""><?=lang('request_payment')?></li>
					<li><span class="icon icon-keyboard_arrow_right"></span></li>
					<li class="active"><?=lang('success')?></li>
				</ul>
			</div>
        </div>
      </div>
<div class="car-page-full">
      <div class="container">
        <div class="col-md-12">
          <div class="row">
		  <?php
          if(isset($_GET['key']) && isset($_GET['PayerID'])){
		  			$increment_id=base64_decode($_GET['key']);
		  		}elseif (isset($_GET['keys'])) {
              $increment_id=base64_decode($_GET['keys']);
          }else {
              $increment_id='';
          }
          $divHide="";// hide div
          $divHideCod="";// hide div
          $msgImg='thankyou-check.png';
          /*if ($paymentMethodcod=='cod' && $shop_flag==2) {
              $divHide="hide";
          } else {
          }*/
              $divHideCod="hide";

          ?>

				<div class="thankyou-page ">
					<div class="thankyou-check <?=$divHideCod?>" id="processImg"><img src="<?php echo SKIN_URL; ?>images/process-tick.png"></div>
					<div class="thankyou-check <?=$divHide?>" id="thankyouImg"><img src="<?php echo SKIN_URL; ?>images/thankyou-check.png"></div>
					<h2 class="cod <?=$divHideCod?>"><?=lang('cod_verification_required')?></h2>
					<h2 class="thanky <?=$divHide?>"><?=lang('thank_you_for_your_order_payment')?></h2>
					<div class="thankyou-order-msg <?=$divHide?>">
						<p>
						<?=lang('order_number_is')?>: <?php echo ($increment_id!='')?'#'.$increment_id:''; ?></p>
					</div><!-- received-email-msg -->

					<!-- <div class="thankyou-email-msg <?=$divHide?>">
					 <p><i class="icon-mail_outline"></i> <?=lang('you_will_received_tracking_details')?></p>
					</div> -->
					<!-- received-email-msg -->
					<!-- <div class="continue-shop-btn <?=$divHide?>">
						<a href="<?php echo base_url(); ?>"><button class="black-btn"><?=lang('continueshopping')?></button></a>
					</div> -->

				<!--end cod confirm otp -->
				</div>

          </div><!-- row -->
        </div><!-- col-md-12 -->
      </div><!-- container -->
    </div>

<?php $this->load->view('common/footer'); ?>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>checkout.js"></script>
<script>



</script>

<?php
    /*if ($this->session->userdata('checkout_message')) {
        $this->session->unset_userdata('checkout_message');
    } else {
        redirect('/');
    }*/
?>


</body>
</html>