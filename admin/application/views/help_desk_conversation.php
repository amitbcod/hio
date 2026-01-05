<?php $this->load->view('common/fbc-user/header'); ?> 



<main class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <?php if($this->session->flashdata('success')): ?>

        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>

    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>

        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>

    <?php endif; ?>



   	<?php if(!empty($help_desk_data)): ?>
		<?php $first_ticket = $help_desk_data[0]; ?>
		<div class="card mb-4">
			<div class="card-header">
				<strong>Ticket: <?= $first_ticket->ticket_id; ?></strong>
			</div>
			<div class="card-body">
				
				<p>
					<strong>Order:</strong> <?= !empty($order) ? $order->increment_id : $first_ticket->order_id; ?> 
					| <strong>Product:</strong> <?= !empty($product) ? $product->product_name : $first_ticket->products; ?>
				</p>

				<hr>
				<h5>Conversation:</h5>
				<ul class="list-unstyled">
					<?php foreach($help_desk_data as $msg): ?>
						<?php if(!empty($msg->message)): ?>
							<li class="mb-3">
								<div class="bg-light p-2 rounded">
									<strong>Customer</strong> 
									<small class="text-muted">(<?= date('d M Y, H:i', $msg->created_at); ?>)</small>
									<p><?= nl2br($msg->message); ?></p>
									<?php if(!empty($msg->attachment)): ?>
										<a href="<?= base_url('uploads/help_desk_attachment/' . $msg->attachment); ?>" target="_blank">Attachment</a>
									<?php endif; ?>
								</div>
							</li>
						<?php endif; ?>

						<?php if(!empty($msg->admin_reply)): ?>
							<li class="mb-3">
								<div class="bg-primary text-white p-2 rounded">
									<strong>Admin</strong> 
									<small class="text-muted">(<?= date('d M Y, H:i', $msg->updated_at); ?>)</small>
									<p><?= nl2br($msg->admin_reply); ?></p>
								</div>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>

				</ul>

				<!-- Admin Reply Form -->
				<form method="POST" action="<?= base_url('CustomerController/update_help_desk'); ?>" class="mt-3">
					<input type="hidden" name="id" value="<?= $first_ticket->id; ?>">

					<div class="form-group">
						<label>Admin Reply</label>
						<textarea name="admin_reply" class="form-control" rows="3" required></textarea>
					</div>
					<button type="submit" class="btn btn-primary btn-sm">Send Reply</button>
				</form>

			</div>
		</div>
	<?php else: ?>
		<p>No conversation found.</p>
	<?php endif; ?>




</main>



<?php $this->load->view('common/fbc-user/footer'); ?>

