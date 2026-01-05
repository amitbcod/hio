<?php $this->load->view('common/header'); ?>

    <div class="main">
      <div class="container">
        <ul class="breadcrumb">
			<li><a href="<?php echo base_url(); ?>">home<?=lang('home')?></a></li>
			<!-- <li><span class="icon icon-keyboard_arrow_right"></span></li> -->
			<li class="">checkout<?=lang('checkout')?></li>
			<!-- <li><span class="icon icon-keyboard_arrow_right"></span></li> -->
			<li class="active">order placed<?=lang('order_placed')?></li>
        </ul>

		  <?php

          if (isset($_GET['sessionId'])) {
              $increment_id=base64_decode($_GET['keys']);
          } elseif ($_GET['key']) {
              $increment_id=base64_decode($_GET['key']);
          } else {
              $increment_id='';
          }
          $divHide="";// hide div
          $divHideCod="";// hide div
          $msgImg='thankyou-check.png';
          if ($paymentMethodcod=='cod' && $shop_flag==2) {
              $divHide="hide";
          } else {
              $divHideCod="hide";
          }

          ?>

        <div class="row margin-bottom-40">
          <div class="col-md-12">
			<div class="content-page shadow">
				<div class="PymtGtwMsgContainer">
					<img  id="processImg" src="<?php echo SKIN_URL; ?>images/process-tick.png" alt="Thank You" class="img-responsive <?=$divHideCod?> thankyou-check">
					<img id="thankyouImg" src="<?php echo SKIN_URL; ?>images/thankyou-check.png" alt="Thank You" class="img-responsive <?=$divHide?> thankyou-check">
					<h2 class="cod <?=$divHideCod?>"><?=lang('cod_verification_required')?></h2>
					<h3 class="thanky <?=$divHide?>">Thank you for your Order!</h3>
					<h4 class="thankyou-order-msg <?=$divHide?>">Your order has been successfully placed.</h4>
					<p class="ordnum thankyou-order-msg <?=$divHide?>">Order Number: <?php echo ($increment_id!='')?'#'.$increment_id:''; ?></p><!--  class="ordnum" -->
					<p class="thankyou-email-msg <?=$divHide?>">Tracking details will be sent to your registered email id, after your order has been dispatched.</p>
					<a class="btn btn-primary" role="button" href="<?php echo base_url(); ?>">Continue Shopping</a>
				</div>
			</div>
          </div>
        </div>

        <?php if ($paymentMethodcod=='cod' && $shop_flag==2) { ?>
		<div class="row margin-bottom-40">
			<div class="col-md-12">
				<div class="content-page shadow">
					<div class="otp-div">
						<div class="opt-password" id="opt-passw" >
							<p><?=lang('since_you_have_placed_a_cod_order')?></p>
							<p class="cod-verification">
							<span> <?=lang('enter_cod_verification_otp')?> </span>
							<input class="form-control" type="text"  name="otp_password" id="otp_password" placeholder="<?=lang('enter_your_otp')?>">
							</p>

							<div class="error-red" id="error-msg"></div>
							<div class="verification-button">
							<input id="valid-otp-btn" class="btn btn-blue" type="button" value="<?=lang('submit')?>" onclick="return ValidOTP();">
							<input id="regenerate-otp-btn" class="btn btn-black" type="button" value="<?=lang('regenerate_otp')?>" onclick="OTP_regenerate();">
							</div>

							<!-- <label style="display: none; position: absolute; left: 57%; top: 57%;" id="Loader">
							<img src="<?php echo 'https://'.$_SERVER['SERVER_NAME'].'/' ?>skin/frontend/zumba/default/images/ajax-loader.gif" style="width:30px; height:30px; position:absolute; bottom:0; right:-34px;">
							</label> -->
						</div>

						<div class="confirm-green" id="success-msg"></div>
						<input type="hidden" name="order_id" id="order_id" value="<?php echo $order_id; ?>">
						<input type="hidden" name="phone_no" id="phone_no" value="<?php echo $billingMobile; ?>">
					</div>
				</div>
			</div>
		</div>
		<?php } ?>

      </div><!-- .container ends -->
  	</div><!-- .main ends -->

<?php $this->load->view('common/footer'); ?>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>checkout.js"></script>

</body>
</html>
