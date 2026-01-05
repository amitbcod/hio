<?php $this->load->view('common/header'); ?>

<div class="order-payment">
	<div class="container">

		<form method="POST" name="order-frm" id="order-frm" action="<?php echo base_url(); ?>CheckoutController/place_order_payment">
			<div class="order-btn-block">
			<button class="black-btn" type="submit" name="place_order">Place Order</button>
				</div> 
				<div class="col-12">
				<div class="goods-data">
					<div class="table-wrapper-responsive">
						<table summary="shopping cart">
							<tr>
								<th class="goods-page-image">Image</th>
								<th class="goods-page-description">Description</th>
								<th class="goods-page-quantity">Quantity</th>
								<th class="goods-page-price">Unit price</th>
								<th class="goods-page-total" colspan="2">Total</th>
							</tr>
							<?php
								$base_image= PRODUCT_THUMB_IMG ;

							?>
							<tr>

								<td class="goods-page-image">
									<a href="<?php echo base_url(); ?>product-detail/<?php echo $value->url_key; ?>" target="_blank"><img src="<?php echo $base_image . "/75cd2ebb63c22c2197929d8e6bd5c056.jpg"	; ?>" alt="The Economist Magazine"></a>
								</td>

								<td class="goods-page-description">
									<h3>The Economist Magazine</h3>
								</td>

								<td class="goods-page-quantity">
									<div class="product-quantity">
									<h3>1</h3>
									</div>
								</td>

								<td class="goods-page-total">
									<strong>14,900.00</strong>
								</td>

								<td class="goods-page-total">
									<strong>14,900.00</strong>
								</td>
							</tr>
							<tr>

								<td class="goods-page-image">
									<a href="<?php echo base_url(); ?>product-detail/<?php echo $value->url_key; ?>" target="_blank"><img src="<?php echo $base_image . "/e7f0be511378091e1f78ae841d069188.png"	; ?>" alt="Harvard Business Review  Magazine Digital and Print"></a>
								</td>

								<td class="goods-page-description">
									<h3>Harvard Business Review  Magazine Digital and Print</h3>
								</td>

								<td class="goods-page-quantity">
									<div class="product-quantity">
									<h3>1</h3>
									</div>

								</td>

								<td class="goods-page-total">
									<strong>9,300.00</strong>
								</td>

								<td class="goods-page-total">
									<strong>9,300.00</strong>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
				<div class="order-btn-block">
			<button class="black-btn" type="submit" name="place_order">Place Order</button>
				</div>
		</form>
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
<script>
	$(document).ready(function() {
	$(".order-payment").parents("body").addClass("order-payment-page");
});
</script>
<style>
.order-btn-block {text-align: center;}
	.order-btn-block button.black-btn {
		background: #e84d1c;
	color: #fff;
	border: 0;
	padding: 10px 70px;
	font-weight: bold;
	margin-bottom: 40px;
	margin: 0 auto 40px;
	}

	.order-payment-page .header .top-cart-block, .order-payment-page .header .search-box {
		display: none;
	}

</style>

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
	var razorpay_order_id = "<?php echo $_GET['rp_order_id']; ?>";

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
