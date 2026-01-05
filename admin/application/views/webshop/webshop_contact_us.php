<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<ul class="nav nav-pills">
		<li class="active"><a href="<?= base_url('webshop/contact-us-requests') ?>">Contact Us Requests</a></li>
		<!-- <li><a href="<?= base_url('webshop/edit-contact-us-text') ?>">Edit Contact Us Text</a></li> -->
	</ul>
	<div class="tab-content">

		<div id="static-tab" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">

			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
				<h1 class="head-name">Contact Us Requests List</h1>
			</div><!-- d-flex -->
			<?php //echo "<pre>"; print_r($contactUsRequests); echo "</pre>"; 
			?>

			<!-- form -->
			<div class="content-main form-dashboard admin-shop-details-table new-height">

				<div class="table-responsive text-center">

					<table class="table table-bordered table-style" id="contactUsTable">

						<thead>

							<tr>
								<th>Name</th>
								<th>Email</th>
								<th>Created At</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($contactUsRequests as $CUR) { ?>
								<tr>

									<td><?= (isset($CUR->customer_id) && $CUR->customer_id != 0) ? "<a href='#'>" . $CUR->name . "</a>" : $CUR->name; ?></td>

									<td><?= $CUR->email ?></td>

									<td><?= date('d/m/Y', $CUR->created_at) . ' | ' . date('H:i a', $CUR->created_at) ?></td>

									<td><a class="link-purple" href="javascript:void(0);" onclick="viewContactUsMsg(<?php echo $CUR->id; ?>)">View</a></td>

								</tr>
							<?php } ?>

						</tbody>

					</table>

				</div>

			</div>

			<!--end form-->

		</div>

	</div>

</main>

<!-- MODAL VIEW -->

<div class="modal fade" id="Modal_View" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h1 class="head-name pad-bottom-20">Contact Us Request</h1>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span></button>
			</div>
			<div class="modal-body">
				<div class="con-req-popup">
					<p class="" id="exampleModalLabel"><span class="h6">Name : </span> <span class="" id="contact_name"></span></p>
					<p class=" mt-3" id="exampleModalLabel"><span class="h6">Email : </span> <span class="" id="contact_email"></span></p>
					<p class=" mt-3" id="exampleModalLabel"><span class="h6">I have a question related to an order : </span> <span class="" id="contact_order_flag"></span></p>
					<p class=" mt-3" id="exampleModalLabel"><span class="h6">Order Number : </span> <span class="" id="contact_orderId"></span></p>
					<p class=" mt-3" id="exampleModalLabel"><span class="h6">Message : </span> <span class="" id="contact_message"></span></p>
					<!-- <p class=" mt-3" id="exampleModalLabel"><span class="h6">Communication Language : </span> <span class="" id="conatct_comm_lan"></span></p> -->
				</div>
			</div>
		</div>
	</div>
	<!--END MODAL VIEW-->

	<script src="<?php echo SKIN_JS; ?>webshop.js"></script>

	<script type="text/javascript">
		$("#contactUsTable").dataTable({
			"ordering": false,

			"language": {

				"infoFiltered": "",

				"search": '',

				"searchPlaceholder": "Search",

				"paginate": {

					next: '<i class="fas fa-angle-right"></i>',

					previous: '<i class="fas fa-angle-left"></i>'

				}

			},

		});

		function viewContactUsMsg(id) {

			//alert(id);

			$('#Modal_View').modal('show');

			$.ajax({

				type: "POST",

				url: BASE_URL + "contact-us-message",

				dataType: "JSON",

				data: {
					id: id
				},

				success: function(response) {

					console.log(response);

					if (response.flag == '1') {

						$('#contact_message').html(response.data.message);
						$('#contact_name').html(response.data.name);
						$('#contact_email').html(response.data.email);
						// $('#conatct_comm_lan').html(response.data.display_name);
						var yes = response.data.order_flag;
						if (yes == '1') {
							$('#contact_order_flag').html('Yes');
							$('#contact_orderId').html(response.data.order_increment_id);
						} else {
							$('#contact_order_flag').html('No');
							$('#contact_orderId').html('-');
						}

					}

				},

				error: function(response) {

					console.log(response);

				}

			});

		}
	</script>

	<?php $this->load->view('common/fbc-user/footer'); ?>