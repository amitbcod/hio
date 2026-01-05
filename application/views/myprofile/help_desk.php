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

				<div class="content-page">

					<div class="row">

						<div class="col-sm-4 col-md-6">

							<h1>Create New Ticket</h1>

						</div>

						<div class="row">

							<div class="col-md-12">

								<form class="default-form" id="customer-personal-info-form" method="POST" action="<?php echo BASE_URL; ?>MyProfileController/helpDeskPost"  enctype="multipart/form-data">
									<div class="row">
										<div class="col-md-4">
											<div class="form-group">
												<label>Subject</label>
												<input type="text" class="form-control" placeholder="" value="" id="subject" name="subject">
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label>Category</label>
												<select id="category_id" name="category_id" class="form-control select2 required-entry" style="width: 100%;">
													<option value="">Select Yellow Market Support</option>
													<option value="1">Error Report</option>
													<option value="2">Yellow Markets Delivery</option>
													<option value="3">Enquiry - General</option>
										        </select>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label>Priority</label>
												<select id="priority_id" name="priority_id" class="form-control select2 required-entry" style="width: 100%;">
													<option value="">Select a priority</option>
													<option value="1">Low</option>
													<option value="2">Medium</option>
													<option value="3">High</option>
													<option value="4">Urgent</option>
												</select>
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-group">
												<label>Message</label>
												<textarea placeholder="" class="form-control" rows="5" name="message" id="message"></textarea>
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-group">
												<label>Attachment (Up to 2.0 MB per image.Acceptable image formats: jpg, jpeg, png)</label>
												<input type="file" class="form-control" placeholder="" id="attachment" name="attachment">
											</div>
										</div>

										<div class="col-md-6">
											<div class="form-group">
												<label>Order</label>
												<select id="order_id" name="order_id" class="form-control select2 required-entry">
													<option value="">Select an order</option>
													<?php 
														// Map status codes to readable text
														$status_labels = [
															0 => 'Complete',
														];

														foreach ($orders as $order): 
															$status_text = isset($status_labels[$order->status]) ? $status_labels[$order->status] : 'Unknown';
													?>
														<option value="<?= $order->order_id; ?>">
															#<?= $order->increment_id; ?> at <?= date('j M Y, H:i', $order->created_at); ?> (MUR <?= number_format($order->grand_total, 2); ?>) - <?= $status_text; ?>
														</option>
													<?php endforeach; ?>
												</select>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label style="display: block;">Products(Select Product to return)</label>
												<select id="product_id" name="product_id" class="form-control select2 required-entry">
													<option value="">Select a product</option>
													<!-- Products will be loaded here dynamically -->
												</select>
											</div>

										</div>
										<div class="col-md-12 text-center">
											<button type="submit" class="btn btn-primary">Submit Ticket</button>
										</div>
									</div><!-- .row ends -->
								</form>
								<!-- END FORM-->
								<?php
									// Group tickets by order_id + product_id
									$grouped_tickets = [];
									foreach ($help_desk_data as $ticket) {
										$key = $ticket->order_id . '_' . $ticket->products;
										if (!isset($grouped_tickets[$key])) {
											$grouped_tickets[$key] = [];
										}
										$grouped_tickets[$key][] = $ticket;
									}
									?>

									<table class="table table-bordered mt-3">
										<thead>
											<tr>
												<th>SR No</th>
												<th>Ticket Id</th>
												<th>Subject</th>
												<th>Category</th>
												<th>Last Activity</th>
												<th>Status</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<?php $sr = 1; ?>
											<?php foreach ($grouped_tickets as $tickets) : ?>
												<?php
												// Use the first ticket for display purposes
												$first_ticket = $tickets[0];
												// Get last activity from the latest updated_at in the group
												$last_activity = max(array_column(array_map(fn($t)=> (array)$t, $tickets), 'updated_at'));
												?>
												<tr>
													<td><?= $sr++; ?></td>
													<td><?= $first_ticket->ticket_id; ?></td>
													<td><?= $first_ticket->subject; ?></td>
													<td>
														<?php
														$categories = [1 => 'Error Report', 2 => 'Yellow Markets Delivery', 3 => 'Enquiry - General'];
														echo $categories[$first_ticket->category] ?? 'Unknown';
														?>
													</td>
													<td><?= date('d M Y, H:i', $last_activity); ?></td>
													<td>
														<?php
														$statusLabels = [0 => 'Not Opened', 1 => 'Open', 2 => 'Closed'];
														echo $statusLabels[$first_ticket->status] ?? '-';
														?>
													</td>
													<td>
														<a href="<?= base_url("MyProfileController/viewTicket/{$first_ticket->order_id}/{$first_ticket->products}") ?>">View</a>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>




							</div>
						</div>
					</div><!-- .content-page ends -->

				</div>


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