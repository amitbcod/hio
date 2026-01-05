<?php $this->load->view('common/header'); ?>

<div class="breadcrum-section">
	<div class="container">
		<div class="breadcrum">
			<ul class="breadcrumb">
				<li><a href="<?php echo base_url(); ?>"><?= lang('home') ?></a></li>
				<li class=""><a href="<?php echo base_url(); ?>cart"><?= lang('cart') ?></a></li>
				<li class="active"><?= lang('checkout') ?></li>
			</ul>
		</div>
	</div>
</div>

<?php
$currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
$currency_symbol = $this->session->userdata('currency_symbol');
$default_currency_flag = $this->session->userdata('default_currency_flag');
?>

<div class="checkout-page-full">
	<div class="container">
		<div class="col-md-12">

			<div class="row">
				<div class="col-md-8 col-lg-8 ">
					<div class="bs-example">

						<div class="accordion-checkout" id="checkout-accordion">
							<?php
							$cartItems = $CartData->cartItems;
							$cartDetails = $CartData->cartDetails;
							if ($this->session->flashdata('msg') != '' && $this->session->flashdata('msg') == 'failed_payment') {
							?>
								<div class="alert alert-danger alert-dismissible fade show" role="alert">
									<?= lang('payment_failed') ?>
									<button type="button" class="close" data-dismiss="alert" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
							<?php
							}
							if (empty($this->session->userdata('LoginID'))) { ?>
								<div class="card active">
									<div class="card-header" id="checkout-register">
										<h2 class="mb-0">
											<button type="button" class="btn collapsed" data-toggle="collapse" data-target="#collapseOne"><i class="fa icon-minus"></i> <?= lang('checkout_as_guest_or_register') ?> </button>
										</h2>
									</div>
									<div id="collapseOne" class="collapse in" aria-labelledby="checkout-register" data-parent="#checkout-accordion">
										<div class="card-body">
											<div class="checkout-guest" id="checkout-guest">
												<input type="hidden" id="restricted_access" name="restricted_access" value="<?php echo $restricted_access->custom_variable->value; ?>">
												<?php if ($restricted_access->custom_variable->value == 'no') { ?>
													<label class="radio-label-checkout"><input class="radio-checkout" type="radio" value="guest" name="select_checkout_method" checked onclick="SetCheckoutMethod(this.value);"> <?= lang('checkout_as_guest') ?>
														<span class="radio-check"></span></label>
												<?php } ?>
												<label class="radio-label-checkout"><input class="radio-checkout" type="radio" value="login" name="select_checkout_method" <?php if ($restricted_access == 'yes' && $customer_id != 0) {
																																												echo "checked";
																																											} ?> onclick="SetCheckoutMethod(this.value);"> <?= lang('already_member') ?> <span class="radio-check"></span></label>
												<?php if ($restricted_access->custom_variable->value == 'no') { ?>
													<label class="radio-label-checkout"><input class="radio-checkout" type="radio" value="register" name="select_checkout_method" onclick="SetCheckoutMethod(this.value);"> <?= lang('register_and_continue') ?> <span class="radio-check"></span></label>
												<?php } ?>

												<div class="sigin-member d-none" id="login-block">
													<h5><?= lang('register_user') ?></h5>
													<form id="sigin-form" method="POST" action="<?php echo BASE_URL; ?>CheckoutController/login">
														<?php (new Login('checkout'))->render(); ?>
													</form>
												</div>


												<div class="sigin-member  d-none" id="register-block">
													<h5> <?= lang('register_and_continue') ?> </h5>
													<form id="signup-form" method="POST" action="<?php echo BASE_URL; ?>CheckoutController/register">
														<?php (new Register('checkout'))->render(); ?>
													</form>
												</div>


												<div class="guestTnc">
													<input type="checkbox" id="agree_chk_guest" name="agree_chk" value="1"> <?= lang('agree_to_all') ?>
													<a href="/page/termsofuse" target="_blank" class="tnc"><?= lang('terms_of_use') ?></a>
													<label id="agree_chk-error-guest" class="error" for="agree_chk"></label>
												</div>

											</div>
											<div class="checkout-btn checkmethod-tab " id="checkmethod-tab">
												<button class="black-btn btn btn-primary" type="button" id="guest-save" onclick="ProceedBilling();"><?= lang('continue') ?></button>
											</div><!-- checkout-btn -->
										</div>
									</div>
								</div><!-- card -->

							<?php } else {  ?>
								<input type="hidden" name="select_checkout_method" value="login">
							<?php  } ?>
							<form method="POST" name="order-frm" id="order-frm" action="<?php echo base_url(); ?>CheckoutController/placeorder">
								<input type="hidden" name="quote_id" id="quote_id" value="<?php echo $QuoteId; ?>">
								<input type="hidden" name="temp_bill_country" id="temp_bill_country" value="">

								<input type="hidden" class="common_vat_no" name="vat_no" id="vat_no" value="">
								<input type="hidden" name="hidden_company_name" id="hidden_company_name" value="">

								<input type="hidden" class="consulation_no" name="consulation_no" id="consulation_no" value="">
								<input type="hidden" class="res_company_name" name="res_company_name" id="res_company_name" value="">
								<input type="hidden" class="res_company_address" name="res_company_address" id="res_company_address" value="">

								<?php if (empty($this->session->userdata('LoginID'))) { ?>
									<input type="hidden" name="checkout_method" id="checkout_method" value="guest">
								<?php } else {  ?>
									<input type="hidden" name="checkout_method" id="checkout_method" value="login">
								<?php  }  ?>

								<?php
								$quote_billing_address = array();
								$quote_shipping_address = array();
								$same_as_billing = 0;
								if (isset($quote_address_data) && count($quote_address_data) > 0) {

									// Set bill address
									$key = array_search('1', array_column($quote_address_data, 'address_type'));
									if (isset($key)) {
										$quote_billing_address[] = $quote_address_data[$key];
									}

									// Set shipping address
									$key = array_search('2', array_column($quote_address_data, 'address_type'));
									if (isset($key)) {
										$quote_shipping_address[] = $quote_address_data[$key];
										if (isset($quote_address_data[$key]->same_as_billing)) {
											$same_as_billing = $quote_address_data[$key]->same_as_billing;
										}
									}
								}
								$componentBillingArr = array('addressList' => $addressList, 'restricted_access' => $restricted_access, 'countryList' => $countryList, 'stateList' => $stateList, 'billing_address_data' => $quote_billing_address, 'quoteData' => $quoteData, 'same_as_billing' => $same_as_billing);
								(new Checkout())->billingAddress($componentBillingArr);

								$componentShippingArr = array('addressList' => $addressList, 'countryList' => $countryList, 'stateList' => $stateList, 'ShipToCountry' => $ShipToCountry, 'shipping_address_data' => $quote_shipping_address, 'quoteData' => $quoteData);
								(new Checkout())->shippingAddress($componentShippingArr);
								?>


								<?php if ($this->session->userdata('session_vat_flag') == 1) { ?>
									<div class="card">
										<div class="card-header" id="shipping-method">
											<h2 class="mb-0">
												<button type="button" disabled id="shipping-tab" class="btn collapsed" data-toggle="collapse" data-target="#collapseShippingMethod"><i class="fa icon-plus"></i> <span class="counter-no"><?= lang('ch_3') ?></span> <?= lang('shipping_methods') ?></button>
											</h2>
										</div>
										<div id="collapseShippingMethod" class="collapse" aria-labelledby="shipping-method" data-parent="#checkout-accordion">
											<div class="card-body">
												<div id="check_shipMethod_error"></div>
												<div class="checkout-payment checkout-shipping-method" id="checkout-shipping-method">

												</div>
												<div class="checkout-btn">
													<button class="black-btn" type="button" id="shipping-method-save"><?= lang('continue') ?></button>
												</div><!-- checkout-btn -->

											</div>
										</div>
									</div><!-- card -->
								<?php } ?>

								<div class="card">
									<div class="card-header" id="payment-method">
										<h2 class="mb-0">
											<button type="button" disabled id="payment-tab" class="btn collapsed" data-toggle="collapse" data-target="#collapseThree"><i class="fa icon-plus"></i>
												<span class="counter-no">
												</span> <?= lang('payment_methods') ?>
											</button>
										</h2>
									</div>
									<div id="collapseThree" class="collapse" aria-labelledby="payment-method" data-parent="#checkout-accordion">
										<div class="card-body">
											<div id="check_pay_error"></div>
											<div class="checkout-payment" id="checkout-payment">
												<?php include('payment_methods.php'); ?>
											</div>
											<div class="checkout-btn">
												<button class="black-btn" type="button" id="payment-save"><?= lang('continue') ?></button>
											</div><!-- checkout-btn -->

										</div>
									</div>
								</div><!-- card -->

								<div class="card">
									<div class="card-header" id="order-review">
										<h2 class="mb-0">
											<button type="button" disabled id="overview-tab" class="btn collapsed" data-toggle="collapse" data-target="#collapseFour"><i class="fa icon-plus"></i>
												<span class="counter-no">
													<?= $this->session->userdata('session_vat_flag') == 1 ? '5' : '4' ?>
												</span> <?= lang('order_review') ?></button>
										</h2>
									</div>
									<div id="collapseFour" class="collapse" aria-labelledby="order-review" data-parent="#checkout-accordion">
										<div class="card-body">
											<div class="checkout-product-added-list">

												<?php if (isset($CartData) && isset($CartData->cartItems) && count($CartData->cartItems) > 0) { ?>


													<ul class="cart-left-box-block">
														<?php foreach ($cartItems as $value) {

															$base_image = ((isset($value->base_image) && $value->base_image != '') ? PRODUCT_THUMB_IMG . $value->base_image : PRODUCT_DEFAULT_IMG);
															$product_variants = (($value->product_variants != '') ? json_decode($value->product_variants) : '');

															$variants = '';
															if (isset($product_variants) && $product_variants != '') {
																foreach ($product_variants as $pk => $single_variant) {
																	foreach ($single_variant as $key => $val) {
																		$variants .= '<span class="variant-item">' . $key . ' - ' . $val . '</span><br>';
																	}
																}
															}
														?>
															<li>
																<div class="cart-images"><img src="<?php echo $base_image; ?>"></div>
																<div class="cart-table-right">
																	<h2 class="head-cart"><?php echo ((isset($value->other_lang_name) && $value->other_lang_name != '') ? $value->other_lang_name : $value->product_name);
																							?></h2>
																	<?php if (isset($product_variants) && $product_variants != '') { ?>
																		<p class="grey-light-text"><?= rtrim($variants, ", "); ?></p>
																	<?php } ?>
																	<div class="price">
																		<div class="price-cart-table"><?php
																										echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($value->price, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($value->price, 2));
																										?></div>
																		<div class="qty-box-cart"> <span><?= lang('quantity') ?></span> <?= $value->qty_ordered ?> </div>
																	</div>
																	<?php if (SHOP_ID !== 1) { ?>
																		<p class="delivery-time"><?= ($value->estimate_delivery_time != '') ? lang('delivery_in') . ' ' . $value->estimate_delivery_time . ' ' . lang('days') : ''; ?> </p>
																	<?php } ?>
																</div><!-- cart-table-right -->
															</li>
														<?php } ?>


													</ul>
												<?php } ?>

												<div class="checkout-total" id="checkout-total">

													<?php include('order_review_totals.php'); ?>
												</div><!-- checkout-total -->

											</div><!-- checkout-product-added-list -->

											<div class="checkout-btn placeorder-btn">
												<div class="left-checkout-sec">
													<?php
													if (isset($acc_inv_flag) && $acc_inv_flag == 1) {
														if ($request_for_invoice_access_data == 'yes') {
															echo '<input type="hidden" name="request_for_invoice" id="request_for_invoice" value="1" >  ';
														} else {
													?>
															<!-- <p><span class="request-for-invoice"><label class="checkbox-label"><input type="checkbox" name="request_for_invoice" id="request_for_invoice" value="1">
																		<span class="checked"></span> Request for Invoice </label>
																</span></p> -->
													<?php
														}
													}
													?>
												</div>
												<div class="right-checkout-sec">
													<?php
													if ($order_check_termsconditions == 'yes') {
													?>
														<p>
															<span class="request-for-invoice">
																<input type="checkbox" id="agree_chk_guest" name="agree_chk" value="1" required> <?= lang('agree_to') ?>
																<a href="<?php echo base_url(); ?>page/tnc" target="_blank" class="tnc"><?= lang('terms_conditions') ?></a>
															</span>
														</p>
													<?php } ?>
													<p>
														<span class="request-for-invoice subscribe-to-newsletter"><label class="checkbox-label"><input type="checkbox" name="subscribe_to_newsletter" id="subscribe_to_newsletter" value="1">
																<span class="checked"></span> <?= lang('subscribe_to_newsletter') ?> </label>
														</span>
													</p>
													<?php if ($emailQuotData == 'protonmail.com') {
													} else { ?>
														<button class="black-btn" type="submit" name="place_order" id="place_order" disabled><?= lang('place_order') ?></button>
													<?php } ?>
												</div>
											</div><!-- checkout-btn -->

										</div>
									</div>
								</div><!-- card -->
								<input type="hidden" class="" name="grand_total_amount" id="grand_total_amount" value="<?php echo $cartDetails->grand_total; ?>">
								<input type="hidden" class="" name="hidden_email_id" id="hidden_email_id" value="<?= $hidden_email_id ?>">
								<input type="hidden" class="" name="hidden_mobile_no" id="hidden_mobile_no" value="<?= $hidden_mobile_no ?>">
							</form>

						</div><!-- checkout-accordion -->

					</div><!-- bs-example -->
				</div><!-- col-sm-9 -->

				<div class="col-md-4 col-lg-4 " id="checkout-sidebar">
					<?php $this->load->view('cart/cart_total'); ?>
				</div><!-- col-sm-3 -->

			</div><!-- row -->
		</div><!-- col-md-12 -->
		<?php
		if ((isset($_GET['paymentURL']) && $_GET['paymentURL'] > 0)) {
			$production_url = $_GET['paymentURL'];
		?>
			<iframe src="<?php echo $production_url ?>" id="paymentFrame" width="482" height="450" frameborder="0" scrolling="No"></iframe>
		<?php } ?>



	</div><!-- container -->




</div>


<?php $this->load->view('common/footer'); ?>
<?php if ($this->session->userdata('session_vat_flag') == 1) { ?>
	<script type="text/javascript" src="<?php echo SKIN_JS; ?>checkout_eu_shop.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<?php } else { ?>
	<script type="text/javascript" src="<?php echo SKIN_JS; ?>checkout.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<?php } ?>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>checkout-login.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script>
	$(document).ready(function() {
		<?php if ($this->session->userdata('checkout_error')) { ?>
			var ck_error = "<?php echo $this->session->userdata('checkout_error'); ?>";
			swal('Error', ck_error, 'error');
		<?php
			$this->session->unset_userdata('checkout_error');
		} ?>
	});
</script>

<!-- <script src="https://checkout.razorpay.com/v1/checkout.js"></script> -->
<!-- <script type="text/javascript">
jQuery(document).ready(function(){
	  <?php if ((isset($_GET['order_id']) && $_GET['order_id'] > 0) && (isset($_GET['increment_id']) && $_GET['increment_id'] > 0) && (isset($_GET['payment_id']) && $_GET['payment_id'] == 3)) { ?>
		  var total = (jQuery('form#payment-form').find('input#grand_total_amount').val() * 100);
			var merchant_order_id = "<?php echo $_GET['increment_id']; ?>";
			var card_holder_name_id = jQuery('form#payment-form').find('input#name').val();
			var card_number = jQuery('form#payment-form').find('input#cardNumber').val();
			var card_expiry = jQuery('form#payment-form').find('input#expiryDate').val();
			var card_cvv = jQuery('form#payment-form').find('input#cvv').val();
			var merchant_total = ($('#grand_total_amount').val() * 100);
			//var merchant_amount = jQuery('form#razorpay-frm-payment').find('input#amount').val();
			var currency_code_id = "<?php echo CURRENCY_CODE; ?>";
			var key_id = "<?php echo $razorpay_api_key; ?>";
			var store_name = 'Shopinshop';
			var store_description = 'Payment';
			var store_logo = '<?php echo SKIN_URL; ?>images/mania-logo.png';
			var email_id = $('#hidden_email_id').val();
			var mobile_no = $('#hidden_mobile_no').val();

			var order_id="<?php echo $_GET['order_id']; ?>";
			var payment_id="<?php echo $_GET['payment_id']; ?>";
			var razorpay_order_id  = "<?php echo $_GET['rp_order_id']; ?>";

			var razorpay_options = {
				key: key_id,
				amount: merchant_total,
				name: store_name,
				description: store_description,
				//image: store_logo,
				order_id: razorpay_order_id,
				netbanking: true,
				currency: currency_code_id,
				prefill: {
					number: card_number,
					expiry: card_expiry,
					name: card_holder_name_id,
					cvv: card_cvv,
					//method: card,
					email: email_id,
					contact: mobile_no
				},
				notes: {
					soolegal_order_id: merchant_order_id,
				},
				handler: function (transaction) {
					//console.log(transaction)
					jQuery.ajax({
						url:BASE_URL+"CheckoutController/razorpaycallback",
						type: 'post',
						data: {razorpay_payment_id: transaction.razorpay_payment_id,razorpay_order_id:transaction.razorpay_order_id ,razorpay_signature:transaction.razorpay_signature, merchant_order_id: merchant_order_id, card_holder_name_id: card_holder_name_id, merchant_total: merchant_total, currency_code_id: currency_code_id,order_id:order_id,payment_id:payment_id,email_id:email_id,mobile_no:mobile_no},
						dataType: 'json',
						success: function (response) {

							//console.log(response);return false;

							if(response.flag == 1) {

								//alert(response.redirect);


								setTimeout(function() {
										//window.location.href =noHashURL+'#status';
										//location.reload();
										window.location.href=response.redirect;
								});

							}else{

								$.toast({
									heading: '',
									text: response.msg,
									showHideTransition: 'slide',
									icon: 'error'
								  });

								setTimeout(function() {
										//window.location.href =noHashURL+'#status';
										//location.reload();
										window.location.href=response.redirect;
								});



							}
						}
					});
				},
				"modal": {
					"ondismiss": function () {
						// code here
						//location.reload()
					}
				}
			};
			// obj
			var objrzpv1 = new Razorpay(razorpay_options);
			objrzpv1.open();
				e.preventDefault();

	  <?php } ?>
});
</script> -->

</body>

</html>