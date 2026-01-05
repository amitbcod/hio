<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('seller/products/breadcrums'); ?>

	<div class="tab-content">
		<?php  $this->load->view('seller/products/products'); ?>

		<?php  $this->load->view('seller/products/add_new_type'); ?>
		<!-- add new tab -->
		<div id="dropshipping-products" class="tab-pane fade">
			<h3>Menu 2</h3>
			<p></p>
		</div>
	</div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
