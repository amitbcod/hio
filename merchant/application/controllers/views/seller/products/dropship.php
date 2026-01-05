<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('seller/products/breadcrums'); ?>
	
	<div class="tab-content">
		<?php  $this->load->view('seller/products/dropship-products'); ?>
		
	</div>
</main>
  
<?php $this->load->view('common/fbc-user/footer'); ?>