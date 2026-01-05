<?php $this->load->view('common/fbc-user/header'); ?>
<style>
	.loaderiamge {
		width: 100%;
		height: 100%;
		position: fixed;
		z-index: 9999;
		opacity: 0.5;
	}
</style>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<div id="loader" class="loaderiamge" style="display: none;">
		<img src="https://parkmapped.com/uploads/loader/loader.gif">
	</div>
	<?php $this->load->view('webshop/order/breadcrums'); ?>
	<div class="tab-content">
		<div id="new-orders" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">
			<?php $this->load->view('b2b/order/order-top-info'); ?>
			<input type="hidden" id="current_tab" name="current_tab" value="<?php echo $current_tab; ?>">
			<input type="hidden" id="order_id" name="order_id" value="<?php echo $OrderData->order_id; ?>">
			<!-- form -->
			<div class="content-main form-dashboard">
				<?php $this->load->view('b2b/order/order-customer-info'); ?>
				<?php if ($OrderData->is_split != 1) { ?>
					<div id="order-item-outer">
						<?php $this->load->view('b2b/order/order-items'); ?>
					</div>
				<?php } ?>
				<?php if ($OrderData->status == 4 || $OrderData->status == 5 || $OrderData->status == 6 || $OrderData->status == 3) {
				} else { ?>
					<?php if (empty($this->session->userdata('userPermission')) || in_array('b2webshop/orders/write', $this->session->userdata('userPermission'))) { ?>
						<div class="save-discard-btn pad-bottom-20">
							<?php
							if (isset($PublisherPayment->payment_initiated) && $PublisherPayment->payment_initiated == 2) {
								// print_R($PublisherPayment);
							?>
								<!-- <button name="cancel_order_btn" class="purple-btn" data-toggle="modal" id="cancel_order_btn" data-id="<?php echo $OrderData->order_id; ?>" value="<?php echo $OrderData->order_id; ?>" data-target="#cancel-order-modal">Cancel Order</button> -->
								<!-- <button class="purple-btn" id="initiate-payment-btn" onclick="CancelOrder(<?php echo $OrderData->order_id; ?>,'<?php echo $OrderData->increment_id; ?>' );">Cancel Order</button> -->
								<?php if ($PublisherDetails->publication_name == 'Harvard Business Review') {
								?>
								<?php
								} else { ?>
									<!-- <button class="purple-btn" id="initiate-payment-btn" onclick="Initiatepayment(<?php echo $OrderData->order_id; ?>,'<?php echo $OrderData->increment_id; ?>',<?php echo $OrderData->publisher_id; ?> );">Initiate Payment</button> -->
								<?php } ?>
							<?php
							} else if (!isset($PublisherPayment)) {
							?>
								<!-- <button name="cancel_order_btn" class="purple-btn" data-toggle="modal" id="cancel_order_btn" data-id="<?php echo $OrderData->order_id; ?>" value="<?php echo $OrderData->order_id; ?>" data-target="#cancel-order-modal">Cancel Order</button> -->

								<!-- <button  name="cancel_order_btn" class="btn btn-primary" data-toggle="modal" id="cancel_order_btn" data-id="<?php echo $order->order_id; ?>" value="<?php //echo $order->order_id; 
																																															?>" data-target="#cancel-order-modal">Cancel Order</button>  -->
								<?php if ($PublisherDetails->publication_name == 'Harvard Business Review') {
								?>
								<?php
								} else { ?>
									<!-- <button class="purple-btn" id="initiate-payment-btn" onclick="Initiatepayment(<?php echo $OrderData->order_id; ?>,'<?php echo $OrderData->increment_id; ?>',<?php echo $OrderData->publisher_id; ?> );">Initiate Payment</button> -->
								<?php } ?>
							<?php
							}
							?>
							<?php
							if (isset($PublisherPayment->payment_initiated) && $PublisherPayment->payment_initiated == 1 && $PublisherPayment->payment_done == 2) {
								// print_R($PublisherPayment);
							?>
								<!-- <button class="purple-btn" id="initiate-payment-btn" onclick="proceedpayment('<?php echo $PublisherPayment->id ?>' );">Procced To Pay</button> -->
							<?php
							}
							?>
							<!-- <button class="purple-btn" disabled="" id="confirm-order-btn" disabled onclick="ConfirmOrder(<?php echo $OrderData->order_id; ?>);">Confirm Order </button> -->

							<!-- <button class="purple-btn" type="button" id="split-order-btn" onclick="OpenSplitOrderPopup(<?php echo $OrderData->order_id; ?>);">Split Order </button> -->
						</div>
					<?php } ?>
				<?php } ?>
			</div>
			<!--end form-->
		</div>
	</div>
	<div id="cancel-order-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Cancel Order</h4>
				</div>

				<div class="modal-body">
					<h5>Are you sure you want to cancel this order ?</h5>
					<form id="cancel-order-form" method="POST" action="<?php echo BASE_URL; ?>MyOrdersController/cancelOrder">
						<div class="cancel-order-form">
							<div class="form-box">
								<textarea class="form-control" name="cancel_reason" id="cancel_reason" placeholder="Reason for Cancellation*" required="required"></textarea>
								<input type="hidden" id="order_id" name="order_id" value="<?php echo $OrderData->order_id; ?>">
							</div><!-- form-box -->

							<div class="signin-btn">
								<input type="submit" class="black-btn blue-btn" name="submit" id="submit_cancel_order" value="Confirm">
								<input type="button" class="black-btn blue-btn" data-dismiss="modal" name="cancel" id="cancel" value="Cancel">
							</div><!-- signin-btn -->
						</div><!-- sigin-form -->
					</form>
				</div>
			</div>
		</div>
	</div>
</main>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>b2b_order_detail.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>b2b-order-item.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script type="text/javascript">
	function initiatepaymentform() {
		console.log("initiatepaymentform");
		$('#initiate_payment_form').validate({
			ignore: [],
			rules: {
				beneficiary_name: {
					required: true,
				},
				bene_acc_no: {
					required: true,
				},
				bene_ifsc_code: {
					required: true,
				},
			}


		});
		var form = $('#initiate_payment_form');
		$.ajax({
			url: BASE_URL + "B2BOrdersController/InitiateOrder",
			type: "POST",
			datatype: 'json',
			data: form.serialize(),
			success: function(response) {
				$('#modal').modal('hide');
				var response = jQuery.parseJSON(response);
				// console.log(response.message,response,typeof(response));
				if (response.status == 200) {
					swal({
							title: "Success",
							icon: "success",
							text: response.message,
							buttons: true,
						},
						function() {
							location.reload();
						})
				} else {

					swal({
							title: "warning",
							html: true,
							icon: "error",
							text: response.message,
							buttons: true,
						},
						function() {
							location.reload();
						})
				}

			}
		});
		// code
	}

	function Initiatepayment(order_id, inc_id, publisher_id) {
		// alert(order_id);
		// return false;
		if (order_id != '') {
			$.ajax({
				url: BASE_URL + "B2BOrdersController/InitiateOrderPopup",
				type: "POST",
				data: {
					publisher_id: publisher_id,
					order_id: order_id,
					inc_id: inc_id
				},
				success: function(response) {
					if (response != 'error') {
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					} else {
						return false;
					}

				}
			});
		} else {
			return false;
		}

	}

	function proceedpayment(id) {
		// alert(order_id);
		// return false;
		if (order_id != '') {
			$.ajax({
				url: BASE_URL + "B2BOrdersController/ProceedPaymentPopup",
				type: "POST",
				data: {
					id: id
				},
				beforeSend: function() {

					$('#ajax-spinner').show();
				},
				success: function(response) {
					$('#ajax-spinner').hide();
					if (response != 'error') {
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					} else {
						return false;
					}

				}
			});
		} else {
			return false;
		}

	}


	$(document).on('click', '#categorybtn', function(event) {
		// alert("Handler for .submit() called.");
		event.preventDefault();
		console.log(this);
		$('#initiate_payment_form').validate({
			ignore: [],
			rules: {
				beneficiary_name: {
					required: true,
				},
				bene_acc_no: {
					required: true,
				},
				bene_ifsc_code: {
					required: true,
				},
			}


		})
		var form = $('#initiate_payment_form');
		// $.ajax({
		// 	url: BASE_URL + "B2BOrdersController/InitiateOrder",
		// 	type: "POST",
		// 	datatype: 'json',
		// 	data: form.serialize(),
		// 	success: function(response) {
		// 		$('#modal').modal('hide');
		// 		var response = jQuery.parseJSON(response);
		// 		// console.log(response.message,response,typeof(response));
		// 		if (response.status == 200) {
		// 			swal({
		// 					title: "Success",
		// 					icon: "success",
		// 					text: response.message,
		// 					buttons: true,
		// 				},
		// 				function() {
		// 					location.reload();
		// 				})
		// 		} else {

		// 			swal({
		// 					title: "warning",
		// 					html: true,
		// 					icon: "error",
		// 					text: response.message,
		// 					buttons: true,
		// 				},
		// 				function() {
		// 					location.reload();
		// 				})
		// 		}

		// 	}
		// });
		// code
	});

	function paymentdone(id) {
		// alert(id);
		$.ajax({
			url: BASE_URL + "B2BOrdersController/PaymentDone",
			type: "POST",
			data: {
				id: id,
				utr_no: utr_no
			},
			beforeSend: function() {

				$('#ajax-spinner').show();
			},
			success: function(response) {
				$('#ajax-spinner').hide();
				$('#modal').modal('hide');
				var response = jQuery.parseJSON(response);
				// console.log(response.message,response,typeof(response));
				if (response.status == 200) {
					swal({
							title: "Success",
							icon: "success",
							text: response.message,
							buttons: true,
						},
						function() {
							location.reload();
						})
				} else {

					swal({
							title: "warning",
							html: true,
							icon: "error",
							text: response.message,
							buttons: true,
						},
						function() {
							location.reload();
						})
				}

			}
		});
	}
	$(document).on('submit', '#payment_done_form', function(event) {
		// alert("Handler for .submit() called.");
		event.preventDefault();
		console.log(this);
		$('#payment_done_form').validate({
			ignore: [],
			rules: {
				beneficiary_name: {
					required: true,
				},
			}

		})
		var form = $('#payment_done_form');
		$.ajax({
			url: BASE_URL + "B2BOrdersController/PaymentDone",
			type: "POST",
			datatype: 'json',
			data: form.serialize(),
			success: function(response) {
				$('#modal').modal('hide');
				var response = jQuery.parseJSON(response);
				// console.log(response.message,response,typeof(response));
				if (response.status == 200) {
					swal({
							title: "Success",
							icon: "success",
							text: response.message,
							buttons: true,
						},
						function() {
							location.reload();
						})
				} else {

					swal({
							title: "warning",
							html: true,
							icon: "error",
							text: response.message,
							buttons: true,
						},
						function() {
							location.reload();
						})
				}

			}
		});
		// code
	});

	$(document).on('submit', 'form#initiate_payment_form', function(event) {
		// alert("Handler for .submit() called.");
		event.preventDefault();
		console.log(this);
		$(this).validate({
			ignore: [],

			rules: {
				beneficiary_name: {
					required: true,
				},
			}

		})
		// code
	});
	$(document).ready(function() {


		$("#initiate_payment_form").submit(function(event) {
			// alert("Handler for .submit() called.");
			event.preventDefault();
		});
	});

	$('#cancel-order-form').submit(function(e) {

		e.preventDefault();
		var fd = new FormData($('#cancel-order-form')[0]);
		var order_id = (fd.get('order_id'));

		if (order_id != '') {
			$.ajax({
				type: "POST",
				dataType: "html",
				url: BASE_URL + "B2BOrdersController/CancelORderRequest",
				data: fd,
				processData: false,
				contentType: false,
				//async:false,
				beforeSend: function() {
					// $('#ajax-spinner').show();
				},
				success: function(response) {
					console.log(response);
					var response1 = JSON.parse(response);
					if (response1.flag == 1) {
						$('#cancel-order-modal').modal('hide');
						swal({
							title: "",
							icon: "success",
							text: response1.message,
							//buttons: false,
							timer: 3000
						}).then(function() {
							location.reload();
						});

					} else {
						//grecaptcha.reset();
						swal({
							title: "",
							icon: "error",
							text: response1.message,
							//buttons: false,
							timer: 3000
						}).then(function() {
							location.reload();
						});

						// setTimeout(function() {
						// //window.location.href = response.redirect;

						// }, 1000);
					}
				},
				error: function(error) {
					console.log(error);
				}
			});
		} else {
			return false;
		}


	});
</script>
<?php $this->load->view('common/fbc-user/footer'); ?>