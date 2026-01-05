<?php $this->load->view('common/header'); ?>
<div class="breadcrum-section">
  	<div class="container">
		<div class="breadcrum">
			<ul>
				<li><a href="<?php echo base_url(); ?>">Home</a></li>
				<li><span class="icon icon-keyboard_arrow_right"></span></li>
				<li class="active">My Profile</li>
			</ul>
		</div>
    </div>
</div><!-- breadcrum section -->

<div class="my-profile-page-full">
	<div class="container">
  		<div class="row">
			<?php $this->load->view('common/profile_sidebar'); ?>

			<div class="col-md-9 col-lg-9 ">
				<div class="wishlist-listing">
				<?php (new WishlistProducts())->render(); ?>
				</div><!-- wishlist-listing -->
			</div><!-- col-md-9 -->

		</div><!-- row -->
  	</div><!-- container -->
</div><!-- my-profile-page-full -->

<?php $this->load->view('common/footer'); ?>