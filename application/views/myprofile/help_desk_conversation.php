<?php $this->load->view('common/header'); ?>



<div class="breadcrum-section">

	<div class="container">

		<div class="breadcrum">

			<ul class="breadcrumb">

				<li><a href="<?php echo base_url(); ?>">Home</a></li>

				<!--<li><span class="icon icon-keyboard_arrow_right"></span></li>-->

				<li class="active">Help Desk</li>

			</ul>

		</div>

	</div>

</div><!-- breadcrum section -->





<div class="my-profile-page-full">

	<div class="container">

		<div class="row">

			<?php $this->load->view('common/profile_sidebar'); ?>



			<div class="col-sm-9 col-md-9">

				<?php if(!empty($help_desk_data)) : ?>
					<?php $first_ticket = $help_desk_data[0]; ?>
					<div class="card mb-4">
						<div class="card-header">
							<strong>Ticket: <?= $first_ticket->ticket_id; ?></strong>
							<!-- <span class="float-right">
								Status: <?= $statusLabels[$first_ticket->status] ?? '-'; ?>
							</span> -->
						</div>
						<div class="card-body">
							<p>
								<strong>Order:</strong> <?= !empty($order) ? $order->increment_id : $first_ticket->order_id; ?> 
								| <strong>Product:</strong> <?= !empty($product) ? $product->product_name : $first_ticket->products; ?>
							</p>


							<hr>
							<h5>Conversation:</h5>
							<ul class="list-unstyled">
								<?php foreach($help_desk_data as $msg) : ?>
									<?php if(!empty($msg->message)) : ?>
										<li class="mb-3">
											<div class="bg-light p-2 rounded">
												<strong>Customer</strong> <small class="text-muted">(<?= date('d M Y, H:i', $msg->created_at); ?>)</small>
												<p><?= nl2br($msg->message); ?></p>
											</div>
										</li>
									<?php endif; ?>
									<?php if(!empty($msg->admin_reply)) : ?>
										<li class="mb-3">
											<div class="bg-primary text-white p-2 rounded">
												<strong>Admin</strong> <small class="text-muted">(<?= date('d M Y, H:i', $msg->updated_at); ?>)</small>
												<p><?= nl2br($msg->admin_reply); ?></p>
											</div>
										</li>
									<?php endif; ?>
								<?php endforeach; ?>
							</ul>

							<!-- New Query Form -->
							<form method="POST" id="customer-personal-info-form" action="<?= base_url('MyProfileController/helpDeskPost'); ?>" enctype="multipart/form-data" class="mt-3">
								<input type="hidden" name="subject" value="<?= $first_ticket->subject; ?>">
								<input type="hidden" name="category_id" value="<?= $first_ticket->category; ?>">
								<input type="hidden" name="priority_id" value="<?= $first_ticket->priority; ?>">
								<input type="hidden" name="order_id" value="<?= $first_ticket->order_id; ?>">
								<input type="hidden" name="product_id" value="<?= $first_ticket->products; ?>">

								<div class="form-group">
									<label>New Query</label>
									<textarea name="message" class="form-control" rows="3" required></textarea>
								</div>
								<button type="submit" class="btn btn-primary btn-sm">Send Query</button>
							</form>
						</div>
					</div>
				<?php endif; ?>



		</div><!-- row -->

	</div><!-- container -->

</div><!-- my-profile-page-full -->



<?php $this->load->view('common/footer'); ?>
<script>
$(document).ready(function() {
	$('#order_id').on('change', function() {
		var order_id = $(this).val();

		$.ajax({
			url: '<?= base_url("MyProfileController/get_order_products") ?>',
			type: 'POST',
			data: { order_id: order_id },
			dataType: 'json',
			success: function(response) {
				$('#product_id').empty();
				$('#product_id').append('<option value="">Select a product</option>');

				if(response.length > 0){
					$.each(response, function(index, product){
						$('#product_id').append('<option value="'+product.product_id+'">'+product.name+' (Qty: '+product.qty+')</option>');
					});
				}
			}
		});
	});

	$('#customer-personal-info-form').on('submit', function(e){
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            beforeSend: function(){
                $('#customer-personal-info-form button[type="submit"]').prop('disabled', true).text('Submitting...');
            },
            success: function(response){
                if(response.flag == 1){
					swal({
						title: "",
						icon: "success",
						text: response.msg,
						buttons: false,
						timer: 1000 // auto-close after 1 second
					}).then(() => {
						// Optionally reset the form before reload
						$('#customer-personal-info-form')[0].reset();
						$('#product_id').html('<option value="">Select a product</option>');

						// Reload the page after success
						location.reload();
					});
				} else {
					swal({
						title: "Error",
						icon: "error",
						text: response.msg
					});
				}

            },
            error: function(xhr, status, error){
                console.error(error);
                alert('Something went wrong!');
            },
            complete: function(){
                $('#customer-personal-info-form button[type="submit"]').prop('disabled', false).text('Submit Ticket');
            }
        });
    });
});
</script>

<!-- <script src="<?php echo SKIN_JS ?>myprofile.js?v=<?php echo CSSJS_VERSION; ?>"></script> -->


</body>



</html>