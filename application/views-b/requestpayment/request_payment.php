<?php $this->load->view('common/header'); ?>
<div class="breadcrum-section">
      <div class="container">
			<div class="breadcrum">
				<ul>
					<li><a href="<?php echo base_url(); ?>"><?=lang('home')?></a></li>
					<li><span class="icon icon-keyboard_arrow_right"></span></li>
					<li class="active"><?=lang('request_payment')?></li>
				</ul>
			</div>
        </div>
      </div>
	<div class="car-page-full">
		<div class="container">
			<div class="col-md-12">
				<div class="row">
					<div class="request-page col-md-12">
						<div class="card">
							<div class="card-header" id="payment-method">
								<h2 class="mb-0">
									<button type="button" disabled="" id="payment-tab" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false">
										<?=lang('select_payment_methods')?>
									</button>
								</h2>
							</div>
							<div id="collapseThree" class="collapse show" aria-labelledby="payment-method" data-parent="#checkout-accordion" style="">
							  <form method="POST" name="order-frm-rp" id="order-frm-rp" action="<?php echo base_url(); ?>RequestPaymentController/requestPayment">
							  	<input type="hidden" name="orderId" value="<?=$order_id?>">
							  	<input type="hidden" name="orderId" value="<?=$order_id?>">
								<div class="card-body">
									<div id="check_pay_error"></div>
									<div class="checkout-payment" id="checkout-payment"> 

									<?php
									  if(isset($PaymentMethods) && count($PaymentMethods)>0) {
										foreach ($PaymentMethods as $key => $value) {
										  if ($value->payment_gateway_key=='paypal_express' || $value->payment_gateway_key=='stripe_payment' || $value->payment_id==1 || $value->payment_id==6) { //payment+_id_check
												$title = ((isset($value->display_name) && $value->display_name != "") ? $value->display_name : $value->payment_gateway);
									?>
										<p>
											<label class="radio-label-checkout">
												<input
											class="radio-checkout  single-payment" type="radio"
											value="<?php echo $value->payment_id; ?>"
											name="payment_method"><?php echo $title; ?>
												<span class="radio-check"></span>
											</label>
										</p>
										<?php if (isset($value->message) && $value->message != "") { ?>
											<div class="message-box-payment">
												<?php echo $value->message; ?>
											</div>
										<?php } ?>
										<input type="hidden" name="payment_type_<?php echo $value->payment_id; ?>" id="payment_type_<?php echo $value->payment_id; ?>" value="<?php echo $value->payment_type; ?>">
									<?php

										 }
									     }
									   } 

									?>
									</div>
									<div class="checkout-btn" align="center">
										<button class="black-btn" type="submit" id="payment-request"><?=lang('continue_payment')?></button>
									</div><!-- checkout-btn -->

								</div>
							  </form>
							</div>
						</div>
					</div>
				</div><!-- row -->
			</div><!-- col-md-12 -->
		</div><!-- container -->
	</div>

<?php $this->load->view('common/footer'); ?>
<!-- <script type="text/javascript" src="<?php echo SKIN_JS; ?>checkout.js"></script> -->
<script>
	//payment-request
	$('#order-frm-rp').on('submit', function(e){
		var orderId='<?=$order_id?>';
		// var orderId=$('#order_id').val();
		$('#payment-request').attr('disabled',true);
		var getSelectedValue = document.querySelector(   
                'input[name="payment_method"]:checked');   
        if(getSelectedValue != null) {
        	return true;
        }else{
        	document.getElementById("check_pay_error").innerHTML= "<label class='error'>*Please select payment method</label>";
        	$('#payment-request').attr('disabled',false);
        }
		return false;
	});

</script>
	
</body>
</html>