<?php $this->load->view('common/header'); ?>

<?php
$currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
$currency_symbol = $this->session->userdata('currency_symbol');
$default_currency_flag = $this->session->userdata('default_currency_flag');
?>


<!-- BEGIN HEADER -->
<div class="main">
  <div class="container">
    <ul class="breadcrumb">
      <li><a href="<?php echo base_url(); ?>">Home</a></li>
      <li class=""><a href="<?php echo base_url(); ?>cart">Cart</a></li>
      <li class="active"><?php echo lang('checkout_as_guest_or_register'); ?></li>
    </ul>
    <div class="row margin-bottom-40">
      <!-- BEGIN CONTENT -->
      <div class="col-md-12 col-sm-12">
        <h1><?php echo lang('checkout_as_guest_or_register'); ?></h1>
        <!-- BEGIN CHECKOUT PAGE -->
        <div class="panel-group checkout-page accordion scrollable" id="checkout-page">
          <?php
          $cartItems = $CartData->cartItems;
          $cartDetails = $CartData->cartDetails;

          if ($this->session->flashdata('msg') != '' && $this->session->flashdata('msg') == 'failed_payment') {
          ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <?php echo lang('payment_failed'); ?>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          <?php
          }

          if (empty($this->session->userdata('LoginID'))) { ?>
            <!-- BEGIN CHECKOUT -->
            <div id="checkout-register" class="panel panel-default">
              <div class="panel-heading">
                <h2 class="panel-title">
                  <button type="button" class="accordion-toggle collapsed co-btn" data-toggle="collapse" data-target="#collapseOne">
                    <?php echo lang('checkout_as_guest_or_register'); ?>
                  </button>
                </h2>
              </div>
              <div id="collapseOne" class="panel-collapse collapse in">
                <div class="panel-body row">
                  <div class="col-md-6 col-sm-6">
                    <input type="hidden" id="restricted_access" name="restricted_access" value="<?php echo $restricted_access->custom_variable->value; ?>">
                    <h3><?php echo lang('new_customer'); ?></h3>
                    <p><?php echo lang('checkout_options'); ?>:</p>
                    <div class="radio-list">
                      <?php if ($restricted_access->custom_variable->value == 'no') { ?>
                        <label>
                          <input type="radio" name="select_checkout_method" value="register" onclick="SetCheckoutMethod(this.value);"> <?php echo lang('register_user'); ?>
                        </label>
                      <?php } ?>
                      <label>
                        <input type="radio" name="select_checkout_method" <?php if ($restricted_access == 'yes' && $customer_id != 0) { echo "checked"; } ?> onclick="SetCheckoutMethod(this.value);" value="login"> <?php echo lang('already_member'); ?>
                      </label>
                    </div>
                    <p><?php echo lang('account_notice'); ?></p>
                    <div class="guestTnc">
                      <input type="checkbox" id="agree_chk_guest" name="agree_chk" value="1"> <?php echo lang('agree_to_all'); ?>
                      <a href="/page/termsofuse" target="_blank" class="tnc"><?php echo lang('terms_of_use'); ?></a>
                      <label id="agree_chk-error-guest" class="error" for="agree_chk"></label>
                    </div>
                    <button class="black-btn btn btn-primary" type="button" id="guest-save" onclick="ProceedBilling();"><?php echo lang('continue'); ?></button>
                  </div>

                  <div class="col-md-6 col-sm-6 sigin-member d-none " id="login-block">
                    <div class="rbWrap">
                      <h3><?php echo lang('returning_customer'); ?></h3>
                      <p><?php echo lang('returning_customer_text'); ?></p>
                      <form id="sigin-form" method="POST" action="<?php echo BASE_URL; ?>CheckoutController/login">
                        <?php (new Login('checkout'))->render(); ?>
                      </form>
                    </div>
                  </div>

                  <div class="col-md-6 col-sm-6 sigin-member d-none " id="register-block">
                    <div class="rbWrap">
                      <h3><?php echo lang('register_and_continue'); ?></h3>
                      <form id="signup-form" method="POST" action="<?php echo BASE_URL; ?>CheckoutController/register">
                        <?php (new Register('checkout'))->render(); ?>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- END CHECKOUT -->
          <?php } else {  ?>
            <input type="hidden" name="select_checkout_method" value="login">
          <?php  } ?>


          <!-- BEGIN PAYMENT ADDRESS -->
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
          (new Checkout())->billingAddressNew($componentBillingArr);

          $componentShippingArr = array('addressList' => $addressList, 'countryList' => $countryList, 'stateList' => $stateList, 'ShipToCountry' => $ShipToCountry, 'shipping_address_data' => $quote_shipping_address, 'quoteData' => $quoteData);
          (new Checkout())->shippingAddressNew($componentShippingArr);
          ?>
          <!-- BEGIN SHIPPING ADDRESS -->
          <!-- END SHIPPING ADDRESS -->
<!-- BEGIN SHIPPING METHOD -->
<div id="shipping-method" class="panel panel-default">
  <div class="panel-heading">
    <h2 class="panel-title">
      <button type="button" disabled id="payment-tab" class="btn accordion-toggle collapsed co-btn" data-toggle="collapse" data-target="#collapseThree">
        <span class="counter-no">
        </span> <?php echo lang('payment_methods'); ?>
      </button>
    </h2>
  </div>
  <div id="collapseThree" class="panel-collapse collapse">
    <div class="panel-body row">
      <div class="col-md-12">
        <?php include('payment_methods.php'); ?>
        <button class="btn btn-primary" type="button" id="payment-save"><?php echo lang('continue'); ?></button>
      </div>
    </div>
  </div>
</div>
<!-- END SHIPPING METHOD -->

<!-- BEGIN CONFIRM -->
<div id="confirm" class="panel panel-default">
  <div class="panel-heading">
    <h2 class="panel-title">
      <button type="button" disabled id="overview-tab" class="btn accordion-toggle collapsed co-btn" data-toggle="collapse" data-target="#collapseFour">
        <span class="counter-no">
        </span><?php echo lang('order_review'); ?>
      </button>
    </h2>
  </div>
  <div class="panel-collapse collapse" id="collapseFour">
    <div class="panel-body row">
      <?php if (isset($CartData) && isset($CartData->cartItems) && count($CartData->cartItems) > 0) { ?>
        <div class="col-md-9 col-sm-8">
          <div class="goods-data">
            <div class="table-wrapper-responsive">
              <table summary="shopping cart">
                <tr>
                  <th class="goods-page-image"><?php echo lang('image'); ?></th>
                  <th class="goods-page-description"><?php echo lang('description'); ?></th>
                  <th class="goods-page-quantity"><?php echo lang('quantity'); ?></th>
                  <th class="goods-page-price"><?php echo lang('price'); ?></th>
                  <th class="goods-page-total"><?php echo lang('total'); ?></th>
                </tr>
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
                  <tr>
                    <td class="goods-page-image">
                      <a href="javascript:;"><img src="<?php echo $base_image; ?>" alt="<?php echo $value->product_name; ?>"></a>
                    </td>
                    <td class="goods-page-description">
                      <h3><a href="javascript:;"><?php echo ((isset($value->other_lang_name) && $value->other_lang_name != '') ? $value->other_lang_name : $value->product_name); ?></a></h3>
                      <?php if (isset($product_variants) && $product_variants != '') { ?>
                        <p><?= rtrim($variants, ", "); ?></p>
                      <?php } ?>
                    </td>
                    <td class="goods-page-quantity"><?= $value->qty_ordered ?> </td>
                    <td class="goods-page-price"><strong><?php
                      echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($value->price, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($value->price, 2));
                      ?></strong></td>
                    <td class="goods-page-total"><strong><?php
                      echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($value->total_price, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($value->total_price, 2));
                      ?></strong></td>
                  </tr>
                <?php } ?>
              </table>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-4">
          <div class="clearfix">
            <div class="shopping-total" id="shopping-total">
              <?php include('order_review_totals.php'); ?>
            </div>
            <?php if ($order_check_termsconditions == 'yes') { ?>
              <p>
                <span class="request-for-invoice">
                  <input type="checkbox" id="agree_chk_guest" name="agree_chk" value="1" required> <?php echo lang('agree_to'); ?>
                  <a href="<?php echo base_url(); ?>page/tnc" target="_blank" class="tnc"><?php echo lang('terms_conditions'); ?></a>
                </span>
              </p>
            <?php } ?>
            <p>
              <span class="request-for-invoice subscribe-to-newsletter">
                <label class="checkbox-label">
                  <input type="checkbox" name="subscribe_to_newsletter" id="subscribe_to_newsletter" value="1">
                  <span class="checked"></span> <?php echo lang('subscribe_to_newsletter'); ?>
                </label>
              </span>
            </p>
            <div class="divcent text-center">
              <?php if ($emailQuotData != 'protonmail.com') { ?>
                <button class="btn btn-primary chkout place-order" type="submit" name="place_order" id="place_order" disabled><?php echo lang('place_order'); ?></button>
              <?php } ?>
            </div>
          </div>
        </div>
        <div class="clearfix"></div>
      <?php } ?>
    </div>
  </div>
</div>

<!-- END CONFIRM -->

<!-- BEGIN STEPS -->
<!-- END STEPS -->

<!-- BEGIN PRE-FOOTER -->

<!-- BEGIN FOOTER -->

<!-- END FOOTER -->

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